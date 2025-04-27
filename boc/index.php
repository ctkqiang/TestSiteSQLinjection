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
    $remember = isset($_POST['remember']) ? true : false;

    // 使用预处理语句防止SQL注入
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user;
        
        // 处理"记住我"功能
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (30 * 24 * 60 * 60); // 30天
            
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE id = ?");
            $stmt->execute([$token, date('Y-m-d H:i:s', $expiry), $user['id']]);
            
            setcookie('remember_token', $token, $expiry, '/', '', true, true);
        }

        header('Location: boc/dashboard.php');
        exit;
    } else {
        $error = "用户名或密码错误！请检查后重试。";
    }
}

// 检查记住我cookie
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? AND token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user'] = $user;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>某某银行 - 登录</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #fce4ec, #f3e5f5, #e8eaf6);
            font-family: 'Noto Sans SC', 'Microsoft YaHei', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            width: 420px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 40px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff9a9e, #fad0c4, #fad0c4);
        }
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        .logo {
            text-align: center;
            margin-bottom: 35px;
            position: relative;
        }
        .logo h3 {
            color: #1a1a1a;
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 1.8rem;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .logo p {
            color: #757575;
            font-size: 1rem;
            opacity: 0.8;
        }
        .form-control {
            border: 2px solid #e8eaf6;
            padding: 14px;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.9);
        }
        .form-control:focus {
            border-color: #ff9a9e;
            box-shadow: 0 0 0 4px rgba(255, 154, 158, 0.1);
            background: #fff;
        }
        .form-control::placeholder {
            color: #9e9e9e;
        }
        .flower-icon {
            display: inline-block;
            animation: wobble 2s infinite;
            transform-origin: center;
        }
        .flower-icon:last-child {
            animation-delay: 0.5s;
        }
        @keyframes wobble {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-5deg); }
            75% { transform: rotate(5deg); }
        }
        .input-group-text {
            background: transparent;
            border-color: #e8eaf6;
            color: #ff9a9e;
            border-right: none;
            padding-right: 0;
        }
        .input-group .form-control {
            border-left: none;
            padding-left: 0;
        }
        .input-group .form-control:focus + .input-group-text,
        .input-group .input-group-text + .form-control:focus {
            border-color: #ff9a9e;
            box-shadow: none;
        }
        .btn-outline-secondary {
            border-color: #e8eaf6;
            color: #9e9e9e;
        }
        .btn-outline-secondary:hover {
            background-color: #fce4ec;
            border-color: #ff9a9e;
            color: #ff9a9e;
        }
        .spinner-border {
            margin-right: 8px;
        }
        .bi {
            transition: all 0.3s ease;
        }
        .form-label .bi,
        .form-check-label .bi {
            margin-right: 5px;
            color: #ff9a9e;
        }
        .alert .bi {
            font-size: 1.2rem;
            margin-right: 8px;
        }
        .input-group {
            position: relative;
        }
        .login-btn {
            width: 100%;
            background: linear-gradient(45deg, #ff9a9e, #fad0c4);
            color: white;
            border: none;
            padding: 14px;
            font-size: 1.1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            margin-top: 15px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 154, 158, 0.4);
        }
        .login-btn:active {
            transform: translateY(0);
        }
        .form-check {
            margin-top: 18px;
            margin-bottom: 22px;
        }
        .form-check-label {
            color: #757575;
            cursor: pointer;
            font-size: 0.95rem;
            user-select: none;
        }
        .form-check-input {
            border-color: #ff9a9e;
            width: 1.1em;
            height: 1.1em;
        }
        .form-check-input:checked {
            background-color: #ff9a9e;
            border-color: #ff9a9e;
        }
        .alert {
            border: none;
            border-radius: 12px;
            margin-bottom: 25px;
            padding: 15px;
            background: rgba(255, 82, 82, 0.1);
            color: #ff5252;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert::before {
            content: '⚠️';
            font-size: 1.1rem;
        }
        footer {
            color: #9e9e9e;
            margin-top: 25px;
            font-size: 0.9rem;
        }
        .form-label {
            color: #424242;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo">
        <h3><span class="flower-icon">🌸</span> 某某银行后台登录 <span class="flower-icon">🌸</span></h3>
        <p><i class="bi bi-shield-lock"></i> 请使用您的账户进行登录</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" autocomplete="off" class="login-form">
        <div class="mb-4">
            <label for="username" class="form-label">
                <i class="bi bi-person"></i> 用户名
            </label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                <input type="text" class="form-control" id="username" name="username" placeholder="请输入用户名" required>
            </div>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label">
                <i class="bi bi-key"></i> 密码
            </label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" placeholder="请输入密码" required>
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">
                <i class="bi bi-clock-history"></i> 记住我（30天内自动登录）
            </label>
        </div>
        <button type="submit" class="btn login-btn">
            <i class="bi bi-box-arrow-in-right"></i> 安全登录
        </button>
    </form>

    <footer class="text-center mt-4">
        <small><i class="bi bi-shield-check"></i> © 某某银行 2025 - 保密 & 保安</small>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 密码显示切换
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bi-eye');
        this.querySelector('i').classList.toggle('bi-eye-slash');
    });

    // 表单提交动画
    const form = document.querySelector('.login-form');
    const submitBtn = form.querySelector('.login-btn');
    
    form.addEventListener('submit', function(e) {
        submitBtn.style.width = submitBtn.offsetWidth + 'px';
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 登录中...';
        submitBtn.disabled = true;
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
