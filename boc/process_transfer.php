<?php
// ========== 初始化 ==========
session_start();

// 检查用户是否已登录
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

// 获取用户信息
$user = $_SESSION['user'];

// ========== 数据库配置 ==========
$host = 'localhost';
$db   = 'BANKOFCHINA';
$user_db = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user_db, $pass, $options);
} catch (\PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// ========== 处理转账请求 ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_account = $_POST['recipient_account'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $remark = $_POST['remark'] ?? '';
    $error = null;

    // 验证输入
    if (empty($recipient_account)) {
        $error = '请输入收款账号';
    } elseif ($amount <= 0) {
        $error = '请输入有效的转账金额';
    }

    if (!$error) {
        try {
            // 开始事务
            $pdo->beginTransaction();

            // 检查用户余额
            $stmt = $pdo->prepare("SELECT savings FROM accounts WHERE id = ? FOR UPDATE");
            $stmt->execute([$user['id']]);
            $account = $stmt->fetch();

            if ($account && $account['savings'] >= $amount) {
                // 检查收款账号是否存在
                $stmt = $pdo->prepare("SELECT id FROM accounts WHERE account_number = ?");
                $stmt->execute([$recipient_account]);
                $recipient = $stmt->fetch();

                if ($recipient) {
                    // 扣除转出方金额
                    $stmt = $pdo->prepare("UPDATE accounts SET savings = savings - ? WHERE id = ?");
                    $stmt->execute([$amount, $user['id']]);

                    // 增加收款方金额
                    $stmt = $pdo->prepare("UPDATE accounts SET savings = savings + ? WHERE id = ?");
                    $stmt->execute([$amount, $recipient['id']]);

                    // 记录转账交易
                    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, remark, transaction_date) VALUES (?, '转账', ?, ?, NOW())");
                    $stmt->execute([$user['id'], -$amount, $remark]);

                    // 记录收款交易
                    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, remark, transaction_date) VALUES (?, '收款', ?, ?, NOW())");
                    $stmt->execute([$recipient['id'], $amount, $remark]);

                    // 提交事务
                    $pdo->commit();
                    
                    // 转账成功，返回仪表盘
                    header('Location: dashboard.php?success=1');
                    exit;
                } else {
                    $error = '收款账号不存在';
                    $pdo->rollBack();
                }
            } else {
                $error = '余额不足';
                $pdo->rollBack();
            }
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $error = '转账失败：' . $e->getMessage();
        }
    }

    // 如果有错误，返回仪表盘并显示错误信息
    if ($error) {
        header("Location: dashboard.php?error=" . urlencode($error));
        exit;
    }
}

// 如果不是POST请求，重定向到仪表盘
header('Location: dashboard.php');
exit;