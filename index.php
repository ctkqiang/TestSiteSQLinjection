<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Challenge</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-block p-4 rounded-full bg-blue-50 mb-4">
                    <i class="fas fa-user-circle text-4xl text-blue-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Login </h2>
                <p class="text-gray-500 mt-2">Welcome Back</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-user"></i>
                        </span>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Try SQL injection here"
                            required
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="pl-10 w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Or here..."
                            required
                        >
                    </div>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    Login
                </button>
            </form>

            <?php
            ini_set('max_execution_time', '0');

            // Connect to SQLite database
            $database = new SQLite3('database.db');

            // Intentionally vulnerable login process
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $username = $_POST['username']; // Intentionally unsanitized
                $password = $_POST['password']; // Intentionally unsanitized

                // Vulnerable SQL query
                $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
                
                try {
                    $result = $database->query($query);
                    $success = false;
                    
                    echo "<div class='mt-4 space-y-2'>";
                    
                    // Display all returned rows (useful for UNION-based injections)
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        $success = true;
                        echo "<div class='p-3 bg-blue-50 text-blue-700 rounded-lg'>";
                        echo "User found: " . htmlspecialchars($row['username']) . "<br>";
                        echo "Password: " . htmlspecialchars($row['password']);
                        echo "<br>SQL Injected";
                        echo "</div>";
                    }
                    
                    if ($success) {
                        echo "<script>console.log('hacked');</script>";
                    } else {
                        echo "<div class='p-3 bg-red-100 text-red-700 rounded-lg'>Login failed.</div>";
                    }
                    
                    echo "</div>";
                    
                } catch (Exception $e) {
                    // If SQL injection causes an error, consider it a success too
                    echo "<script>console.log('hacked - SQL error');</script>";
                    echo "<div class='mt-4 p-3 bg-red-100 text-red-700 rounded-lg'>SQL Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            }
            ?>
        </div>
    </div>
</body>
</html>