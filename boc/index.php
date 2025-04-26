<?php
// ========== 初始化 ==========
session_start();

// ========== 数据库配置 ==========
$host = 'localhost';
$db   = 'BANKOFCHINA';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// ========== 处理登录请求 ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 💥 超级脆皮 SQL 注入
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $stmt = $pdo->query($sql);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "用户名或密码错误！";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>某某银行 - 登录</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f2f2;
        }
        .login-container {
            width: 400px;
            margin: 100px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-btn {
            width: 100%;
            background-color: #e60012;
            color: white;
            border: none;
            padding: 10px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo">
        <h3>🌸 某某银行后台登录 🌸</h3>
        <p>请使用您的账户进行登录</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>


    <!-- 
        <?php
            $host = 'localhost';
            $db   = 'BANKOFCHINA';
            $user = 'root';
            $pass = '';
            $charset = 'utf8mb4';
        ?> 
    -->

    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">用户名</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="请输入用户名" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">密码</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="请输入密码" required>
        </div>
        <button type="submit" class="btn login-btn">登录</button>
    </form>

    <footer class="text-center mt-4">
        <small>© 某某银行 2025 - 保密 & 保安</small>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
