<?php
// ========== 初始化 ==========
session_start();

// ========== 数据库连接 ==========
$host = 'localhost';
$db   = 'shopdb';
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

// ========== 加购物车逻辑 ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // 脆皮查询
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $stmt = $pdo->query($sql);
    $product = $stmt->fetch();

    if ($product) {
        $_SESSION['cart'][] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
        ];
    }

    header('Location: index.php?page=checkout');
    exit;
}

// ========== 处理页面路由 ==========
$page = $_GET['page'] ?? 'home';

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>🌸 灵儿的小商城 🌸</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="text-center mb-5">🌸 灵儿的小商城 🌸</h1>

    <?php if ($page === 'home'): ?>

        <!-- 搜索框 -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="hidden" name="page" value="home">
                <input type="text" name="search" class="form-control" placeholder="搜索商品（注入试试看😈）">
                <button type="submit" class="btn btn-primary">搜索</button>
            </div>
        </form>

        <!-- 商品列表 -->
        <div class="row">
            <?php
            $products = [];
            if (isset($_GET['search'])) {
                $search = $_GET['search'];
                $sql = "SELECT * FROM products WHERE name LIKE '%$search%'";
            } else {
                $sql = "SELECT * FROM products";
            }
            $stmt = $pdo->query($sql);
            $products = $stmt->fetchAll();
            ?>

            <?php foreach ($products as $product): ?>
                <div class="col-md-3 mb-4">
                    <div class="card shadow-sm">
                        <img src="https://picsum.photos/seed/<?= rand(1000,9999) ?>/400/300" class="card-img-top product-image" alt="商品图">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text">价格：¥<?= htmlspecialchars($product['price']) ?></p>
                            <a href="?page=detail&id=<?= $product['id'] ?>" class="btn btn-outline-primary w-100">查看详情</a>
                            <form method="POST" class="mt-2">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn btn-success w-100">加入购物车</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($page === 'detail' && isset($_GET['id'])): ?>

        <!-- 商品详情 -->
        <?php
        $id = $_GET['id'];
        $sql = "SELECT * FROM products WHERE id = $id";
        $stmt = $pdo->query($sql);
        $product = $stmt->fetch();
        ?>

        <?php if ($product): ?>
            <div class="card mb-4 shadow">
                <img src="https://picsum.photos/seed/<?= rand(1000,9999) ?>/800/400" class="card-img-top" alt="商品详情图">
                <div class="card-body">
                    <h3 class="card-title"><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="card-text">价格：¥<?= htmlspecialchars($product['price']) ?></p>
                    <p class="card-text">商品编号：<?= htmlspecialchars($product['id']) ?></p>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit" class="btn btn-success">加入购物车</button>
                        <a href="index.php" class="btn btn-secondary">返回首页</a>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <p>商品不存在～</p>
        <?php endif; ?>

    <?php elseif ($page === 'checkout'): ?>

        <!-- 购物车 / 结账页 -->
        <?php
        $cart = $_SESSION['cart'] ?? [];
        $total = array_sum(array_column($cart, 'price'));
        ?>

        <h2 class="mb-4">🛒 你的购物车</h2>

        <?php if (empty($cart)): ?>
            <p class="text-center">你的购物车空空如也喔～</p>
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-primary">返回首页继续逛逛</a>
            </div>
        <?php else: ?>
            <ul class="list-group mb-4">
                <?php foreach ($cart as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($item['name']) ?>
                        <span>¥<?= htmlspecialchars($item['price']) ?></span>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
                    总计
                    <span>¥<?= $total ?></span>
                </li>
            </ul>
            <div class="text-center">
                <button class="btn btn-success">提交订单（假装成功）</button>
                <a href="index.php" class="btn btn-secondary">返回首页</a>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <footer class="text-center mt-5">
        <small>🌸 灵儿的小商城 - 测试专用 🌸</small>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
