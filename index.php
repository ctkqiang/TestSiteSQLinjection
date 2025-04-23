<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录挑战</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <!-- 头部 -->
            <div class="text-center mb-8">
                <div class="inline-block p-4 rounded-full bg-blue-50 mb-4">
                    <i class="fas fa-user-circle text-4xl text-blue-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">登录</h2>
                <p class="text-gray-500 mt-2">欢迎回来</p>
            </div>

            <!-- 登录表单 -->
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">用户名</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-user"></i>
                        </span>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="在此尝试SQL注入"
                            required
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">密码</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="或者在这里..."
                            required
                        >
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    登录
                </button>
            </form>

            <?php
            ini_set('max_execution_time', '0');

            // 连接到SQLite数据库
            $database = new SQLite3('database.db');

            // 故意易受攻击的登录过程
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = $_POST['username']; // 故意不进行净化
                $password = $_POST['password']; // 故意不进行净化

                // 易受攻击的SQL查询
                $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
                
                try {
                    $result = $database->query($query);
                    $success = false;
                    
                    echo "<div class='mt-4 space-y-2'>";
                    
                    // 显示所有返回的行（对基于UNION的注入有用）
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        $success = true;
                        echo "<div class='p-3 bg-blue-50 text-blue-700 rounded-lg'>";
                        echo "找到用户：" . htmlspecialchars($row['username']) . "<br>";
                        echo "密码：" . htmlspecialchars($row['password']);
                        echo "<br>SQL注入成功";
                        echo "</div>";
                    }
                    
                    if ($success) {
                        echo "<script>console.log('已被黑客入侵');</script>";
                    } else {
                        echo "<div class='p-3 bg-red-100 text-red-700 rounded-lg'>登录失败。</div>";
                    }
                    
                    echo "</div>";
                    
                } catch (Exception $e) {
                    // 如果SQL注入导致错误，也视为成功
                    echo "<script>console.log('已被黑客入侵 - SQL错误');</script>";
                    echo "<div class='mt-4 p-3 bg-red-100 text-red-700 rounded-lg'>SQL错误：" . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
            ?>
        </div>
    </div>
</body>
</html>