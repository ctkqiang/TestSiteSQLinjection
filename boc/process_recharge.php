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

// ========== 处理充值请求 ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? '';
    $error = null;

    // 验证输入
    if ($amount <= 0) {
        $error = '请输入有效的充值金额';
    } elseif (!in_array($method, ['alipay', 'wechat', 'unionpay'])) {
        $error = '请选择有效的充值方式';
    }

    if (!$error) {
        try {
            // 开始事务
            $pdo->beginTransaction();

            // 生成充值订单号
            $order_id = date('YmdHis') . rand(1000, 9999);

            // 记录充值订单
            $stmt = $pdo->prepare("INSERT INTO recharge_orders (user_id, order_id, amount, method, status, create_time) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([$user['id'], $order_id, $amount, $method]);

            // 这里应该调用实际的支付接口
            // 为了演示，我们直接模拟支付成功
            $payment_success = true;

            if ($payment_success) {
                // 更新用户余额
                $stmt = $pdo->prepare("UPDATE accounts SET savings = savings + ? WHERE id = ?");
                $stmt->execute([$amount, $user['id']]);

                // 更新订单状态
                $stmt = $pdo->prepare("UPDATE recharge_orders SET status = 'completed', complete_time = NOW() WHERE order_id = ?");
                $stmt->execute([$order_id]);

                // 记录交易
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, remark, transaction_date) VALUES (?, '充值', ?, ?, NOW())");
                $stmt->execute([$user['id'], $amount, "通过{$method}充值"]);

                // 提交事务
                $pdo->commit();
                
                // 充值成功，返回仪表盘
                header('Location: dashboard.php?success=1');
                exit;
            } else {
                $error = '支付失败，请重试';
                $pdo->rollBack();
            }
        } catch (\PDOException $e) {
            $pdo->rollBack();
            $error = '充值失败：' . $e->getMessage();
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