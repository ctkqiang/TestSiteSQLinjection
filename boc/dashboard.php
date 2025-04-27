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

// 获取用户账户信息（这里使用预处理语句防止SQL注入）
$stmt = $pdo->prepare("SELECT savings FROM accounts WHERE id = ?");
$stmt->execute([$user['id']]);
$account = $stmt->fetch();

// 获取最近的交易记录
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC LIMIT 5");
$stmt->execute([$user['id']]);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>某某银行 - 仪表盘</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'PingFang SC', 'Microsoft YaHei', sans-serif;
        }
        .dashboard-container {
            padding: 30px;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            height: 300px;
        }
        .welcome-card {
            background: linear-gradient(45deg, #ff6b6b, #ff8787);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease-in-out;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .stat-card .btn {
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            margin-bottom: 10px;
            border-width: 2px;
        }
        .stat-card .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-card .btn-outline-primary {
            background: linear-gradient(to right, rgba(255,107,107,0.1), rgba(255,135,135,0.1));
            border-color: #ff6b6b;
            color: #ff6b6b;
        }
        .stat-card .btn-outline-primary:hover {
            background: linear-gradient(to right, #ff6b6b, #ff8787);
            border-color: transparent;
            color: white;
        }
        .stat-card .btn-outline-success {
            background: linear-gradient(to right, rgba(87,182,87,0.1), rgba(129,200,129,0.1));
            border-color: #57b657;
            color: #57b657;
        }
        .stat-card .btn-outline-success:hover {
            background: linear-gradient(to right, #57b657, #81c881);
            border-color: transparent;
            color: white;
        }
        .stat-card .btn-outline-info {
            background: linear-gradient(to right, rgba(162,210,255,0.1), rgba(189,224,254,0.1));
            border-color: #a2d2ff;
            color: #a2d2ff;
        }
        .stat-card .btn-outline-info:hover {
            background: linear-gradient(to right, #a2d2ff, #bde0fe);
            border-color: transparent;
            color: white;
        }
        .stat-card .btn i {
            margin-right: 8px;
            font-size: 1.1rem;
            vertical-align: -2px;
        }
        .btn {
            transition: all 0.2s ease-in-out;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .transaction-list {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .icon-circle {
            width: 40px;
            height: 40px;
            background-color: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <!-- 导航栏 -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">🌸 某某银行</a>
            <div class="d-flex">
                <div class="dropdown">
                    <button class="btn btn-link text-dark dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <?= htmlspecialchars($user['username']) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> 设置</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> 退出</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container dashboard-container">
        <!-- 欢迎卡片 -->
        <div class="welcome-card">
            <div class="d-flex align-items-center">
                <div class="icon-circle">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
                <div>
                    <h4 class="mb-1">欢迎回来，<?= htmlspecialchars($user['username']) ?></h4>
                    <p class="mb-0">账户余额：¥ <?= number_format($account['savings'], 2) ?></p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- 快捷操作 -->
            <div class="col-md-4">
                <div class="stat-card">
                    <h5 class="card-title mb-4" style="color: #666; font-weight: 600; font-size: 1.1rem;">✨ 快捷操作</h5>
                    <div class="d-grid gap-3">
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#transferModal"><i class="bi bi-send"></i> 转账</button>
                        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#rechargeModal"><i class="bi bi-cash"></i> 充值</button>
                        <button class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#creditCardModal"><i class="bi bi-credit-card"></i> 信用卡</button>
                    </div>
                </div>
                <!-- 支出分类饼图 -->
                <div class="chart-container">
                    <h5 class="card-title mb-4">支出分类</h5>
                    <canvas id="expenseChart"></canvas>
                </div>
            </div>

            <!-- 账户概览 -->
            <div class="col-md-8">
                <!-- 余额趋势图 -->
                <div class="chart-container mb-4">
                    <h5 class="card-title mb-4">账户余额趋势</h5>
                    <canvas id="balanceChart"></canvas>
                </div>
                <!-- 交易趋势图 -->
                <div class="chart-container mb-4">
                    <h5 class="card-title mb-4">交易趋势</h5>
                    <canvas id="transactionChart"></canvas>
                </div>
                <div class="transaction-list">
                    <h5 class="card-title mb-4">最近交易记录</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>日期</th>
                                    <th>类型</th>
                                    <th>金额</th>
                                    <th>状态</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?= htmlspecialchars($transaction['transaction_date']) ?></td>
                                    <td><?= htmlspecialchars($transaction['type']) ?></td>
                                    <td class="<?= $transaction['amount'] > 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= $transaction['amount'] > 0 ? '+' : '' ?><?= number_format($transaction['amount'], 2) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">成功</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 转账模态框 -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transferModalLabel">转账</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="process_transfer.php" method="POST" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="recipientAccount" class="form-label">收款账号</label>
                            <input type="text" class="form-control" id="recipientAccount" name="recipient_account" required>
                            <div class="invalid-feedback">请输入收款账号</div>
                        </div>
                        <div class="mb-3">
                            <label for="transferAmount" class="form-label">转账金额</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="transferAmount" name="amount" min="0.01" step="0.01" required>
                                <div class="invalid-feedback">请输入有效的转账金额</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="transferRemark" class="form-label">备注</label>
                            <input type="text" class="form-control" id="transferRemark" name="remark">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary">确认转账</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 充值模态框 -->
    <div class="modal fade" id="rechargeModal" tabindex="-1" aria-labelledby="rechargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rechargeModalLabel">充值</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="process_recharge.php" method="POST" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rechargeAmount" class="form-label">充值金额</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" class="form-control" id="rechargeAmount" name="amount" min="0.01" step="0.01" required>
                                <div class="invalid-feedback">请输入有效的充值金额</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="rechargeMethod" class="form-label">充值方式</label>
                            <select class="form-select" id="rechargeMethod" name="method" required>
                                <option value="">请选择充值方式</option>
                                <option value="alipay">支付宝</option>
                                <option value="wechat">微信支付</option>
                                <option value="unionpay">银联</option>
                            </select>
                            <div class="invalid-feedback">请选择充值方式</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-success">确认充值</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 信用卡模态框 -->
    <div class="modal fade" id="creditCardModal" tabindex="-1" aria-labelledby="creditCardModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="creditCardModalLabel">信用卡服务</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">信用卡账单</h6>
                                <small class="text-muted"><i class="bi bi-chevron-right"></i></small>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">分期付款</h6>
                                <small class="text-muted"><i class="bi bi-chevron-right"></i></small>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">信用卡还款</h6>
                                <small class="text-muted"><i class="bi bi-chevron-right"></i></small>
                            </div>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">申请新卡</h6>
                                <small class="text-muted"><i class="bi bi-chevron-right"></i></small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // 初始化图表
    document.addEventListener('DOMContentLoaded', function() {
        // 支出分类饼图
        new Chart(document.getElementById('expenseChart'), {
            type: 'doughnut',
            data: {
                labels: ['购物', '餐饮', '交通', '娱乐', '其他'],
                datasets: [{
                    data: [30, 25, 20, 15, 10],
                    backgroundColor: [
                        '#ff9f89',
                        '#ffd1dc',
                        '#bde0fe',
                        '#a2d2ff',
                        '#cdb4db'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // 余额趋势图
        new Chart(document.getElementById('balanceChart'), {
            type: 'line',
            data: {
                labels: ['1月', '2月', '3月', '4月', '5月', '6月'],
                datasets: [{
                    label: '账户余额',
                    data: [10000, 12000, 11500, 13000, 12500, 14000],
                    fill: true,
                    borderColor: '#ff9f89',
                    backgroundColor: 'rgba(255, 159, 137, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // 交易趋势图
        new Chart(document.getElementById('transactionChart'), {
            type: 'bar',
            data: {
                labels: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'],
                datasets: [{
                    label: '收入',
                    data: [1200, 1900, 1500, 1800, 2000, 1700, 1600],
                    backgroundColor: '#a2d2ff'
                }, {
                    label: '支出',
                    data: [1000, 1600, 1200, 1400, 1800, 1500, 1300],
                    backgroundColor: '#ffd1dc'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    });

    // 表单验证
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>