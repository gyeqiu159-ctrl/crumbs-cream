<?php
require_once __DIR__ . '/auth.php';
admin_require_login();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/products.php';

$pdo = get_db_connection();

$dbError = null;
$orders = [];
$stats = [
    'total'      => 0,
    'new'        => 0,
    'contacted'  => 0,
    'completed'  => 0,
    'cancelled'  => 0,
    'this_week'  => 0,
    'revenue'    => 0.0,  // estimated, from all orders regardless of payment
    'paid_total' => 0.0,  // actual, from orders marked payment_status = paid
];
$dailyCounts = []; // date => count, last 14 days
$sizeCounts  = []; // size => count

if ($pdo === null) {
    $dbError = 'Could not connect to the database. Check config/database.php and make sure MySQL is running.';
} else {
    // ---- Handle status update ----
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['new_status'])) {
        $orderId  = (int) ($_POST['order_id'] ?? 0);
        $newStatus = $_POST['new_status'] ?? '';
        $validStatuses = ['new', 'contacted', 'completed', 'cancelled'];

        if ($orderId > 0 && in_array($newStatus, $validStatuses, true)) {
            try {
                $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
                $stmt->execute([':status' => $newStatus, ':id' => $orderId]);
            } catch (PDOException $e) {
                error_log('Order status update failed: ' . $e->getMessage());
            }
        }
        header('Location: index.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
        exit;
    }

    // ---- Filter (optional) ----
    $filterStatus = $_GET['status'] ?? '';
    $validStatuses = ['new', 'contacted', 'completed', 'cancelled'];
    if (!in_array($filterStatus, $validStatuses, true)) {
        $filterStatus = '';
    }

    try {
        // Overall counts by status
        $stmt = $pdo->query('SELECT status, COUNT(*) AS c FROM orders GROUP BY status');
        foreach ($stmt as $row) {
            $stats[$row['status']] = (int) $row['c'];
            $stats['total'] += (int) $row['c'];
        }

        // This week (last 7 days)
        $stmt = $pdo->query('SELECT COUNT(*) AS c FROM orders WHERE created_at >= (NOW() - INTERVAL 7 DAY)');
        $stats['this_week'] = (int) $stmt->fetch()['c'];

        // Orders per size (for chart + estimated revenue)
        $stmt = $pdo->query('SELECT size, SUM(quantity) AS qty, COUNT(*) AS orders_count FROM orders GROUP BY size');
        foreach ($stmt as $row) {
            $sizeCounts[$row['size']] = (int) $row['orders_count'];
            $unitPrice = get_size_price($row['size']);
            if ($unitPrice !== null) {
                $stats['revenue'] += $unitPrice * (int) $row['qty'];
            }
        }

        // Actual revenue collected via PayMongo (payment_status = paid)
        $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS paid_total FROM orders WHERE payment_status = 'paid'");
        $stats['paid_total'] = (float) $stmt->fetch()['paid_total'];

        // Orders per day, last 14 days (for line chart) — fill in zero days too
        $stmt = $pdo->query(
            "SELECT DATE(created_at) AS d, COUNT(*) AS c
             FROM orders
             WHERE created_at >= (NOW() - INTERVAL 13 DAY)
             GROUP BY DATE(created_at)"
        );
        $rawDaily = [];
        foreach ($stmt as $row) {
            $rawDaily[$row['d']] = (int) $row['c'];
        }
        for ($i = 13; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i day"));
            $dailyCounts[$day] = $rawDaily[$day] ?? 0;
        }

        // Recent orders (filtered list)
        $sql = 'SELECT * FROM orders';
        $params = [];
        if ($filterStatus !== '') {
            $sql .= ' WHERE status = :status';
            $params[':status'] = $filterStatus;
        }
        $sql .= ' ORDER BY created_at DESC LIMIT 100';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

    } catch (PDOException $e) {
        error_log('Dashboard query failed: ' . $e->getMessage());
        $dbError = 'Something went wrong reading the orders table.';
    }
}

$statusLabels = ['new' => 'New', 'contacted' => 'Contacted', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Crumb & Cream</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">

    <div class="admin-shell">
        <!-- ================= SIDEBAR ================= -->
        <aside class="admin-sidebar">
            <a href="index.php" class="logo">
                <span class="logo-mark" aria-hidden="true"></span>
                Crumb &amp; Cream
            </a>
            <nav class="admin-nav">
                <a href="index.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                <a href="../index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Site</a>
                <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
            </nav>
        </aside>

        <!-- ================= MAIN ================= -->
        <main class="admin-main">
            <header class="admin-topbar">
                <div>
                    <h1>Dashboard</h1>
                    <p>Orders overview &amp; performance</p>
                </div>
            </header>

            <?php if ($dbError): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($dbError); ?></div>
            <?php else: ?>

                <!-- ================= STAT CARDS ================= -->
                <section class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fa-solid fa-receipt"></i></div>
                        <div>
                            <span class="stat-value"><?php echo number_format($stats['total']); ?></span>
                            <span class="stat-label">Total Orders</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-new"><i class="fa-solid fa-bell"></i></div>
                        <div>
                            <span class="stat-value"><?php echo number_format($stats['new']); ?></span>
                            <span class="stat-label">New / Unhandled</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-week"><i class="fa-solid fa-calendar-week"></i></div>
                        <div>
                            <span class="stat-value"><?php echo number_format($stats['this_week']); ?></span>
                            <span class="stat-label">Last 7 Days</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-revenue"><i class="fa-solid fa-sack-dollar"></i></div>
                        <div>
                            <span class="stat-value">₱<?php echo number_format($stats['revenue'], 2); ?></span>
                            <span class="stat-label">Estimated Revenue</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon stat-icon-paid"><i class="fa-solid fa-qrcode"></i></div>
                        <div>
                            <span class="stat-value">₱<?php echo number_format($stats['paid_total'], 2); ?></span>
                            <span class="stat-label">Paid via QR (PayMongo)</span>
                        </div>
                    </div>
                </section>

                <!-- ================= CHARTS ================= -->
                <section class="chart-grid">
                    <div class="chart-card chart-card-wide">
                        <h2>Orders — Last 14 Days</h2>
                        <canvas id="ordersTrendChart" height="110"></canvas>
                    </div>
                    <div class="chart-card">
                        <h2>Orders by Status</h2>
                        <canvas id="statusChart" height="220"></canvas>
                    </div>
                    <div class="chart-card">
                        <h2>Orders by Size</h2>
                        <canvas id="sizeChart" height="220"></canvas>
                    </div>
                </section>

                <!-- ================= ORDERS TABLE ================= -->
                <section class="table-card">
                    <div class="table-card-head">
                        <h2>Recent Orders</h2>
                        <div class="filter-pills">
                            <a href="index.php" class="pill <?php echo $filterStatus === '' ? 'active' : ''; ?>">All</a>
                            <?php foreach ($statusLabels as $key => $label): ?>
                                <a href="index.php?status=<?php echo urlencode($key); ?>" class="pill <?php echo $filterStatus === $key ? 'active' : ''; ?>"><?php echo htmlspecialchars($label); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Size</th>
                                    <th>Flavor</th>
                                    <th>Qty</th>
                                    <th>Message</th>
                                    <th>Amount</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr><td colspan="11" class="empty-row">No orders yet.</td></tr>
                                <?php endif; ?>
                                <?php
                                $paymentLabels = ['unpaid' => 'Unpaid', 'paid' => 'Paid', 'expired' => 'Expired', 'failed' => 'Failed'];
                                foreach ($orders as $order):
                                ?>
                                    <tr>
                                        <td>#<?php echo (int) $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($order['contact_info']); ?></td>
                                        <td><?php echo htmlspecialchars($order['size']); ?></td>
                                        <td><?php echo htmlspecialchars($order['flavor'] ?? '—'); ?></td>
                                        <td><?php echo (int) $order['quantity']; ?></td>
                                        <td class="msg-cell"><?php echo $order['message'] ? htmlspecialchars($order['message']) : '<span class="muted">—</span>'; ?></td>
                                        <td><?php echo $order['amount'] !== null ? '₱' . number_format((float) $order['amount'], 2) : '<span class="muted">—</span>'; ?></td>
                                        <td><span class="payment-badge payment-<?php echo htmlspecialchars($order['payment_status']); ?>"><?php echo htmlspecialchars($paymentLabels[$order['payment_status']] ?? $order['payment_status']); ?></span></td>
                                        <td><?php echo htmlspecialchars(date('M j, Y g:ia', strtotime($order['created_at']))); ?></td>
                                        <td>
                                            <form method="POST" class="status-form">
                                                <input type="hidden" name="order_id" value="<?php echo (int) $order['id']; ?>">
                                                <select name="new_status" class="status-select status-<?php echo htmlspecialchars($order['status']); ?>" onchange="this.form.submit()">
                                                    <?php foreach ($statusLabels as $key => $label): ?>
                                                        <option value="<?php echo htmlspecialchars($key); ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($label); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" name="update_status" value="1" class="visually-hidden">Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

            <?php endif; ?>
        </main>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>    
<script>
        var CHART_COLORS = {
            caramel: '#C1873F',
            caramelDark: '#9C6428',
            caramelLight: '#E4B876',
            crumb: '#E4C793',
            cocoa: '#3B2417',
            cocoaSoft: '#6B4A34',
            green: '#5f9e6f',
            red: '#c1573f',
            grid: 'rgba(59, 36, 23, 0.08)'
        };

        <?php if (!$dbError): ?>
        // ---- Orders trend (last 14 days) ----
        new Chart(document.getElementById('ordersTrendChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(fn($d) => date('M j', strtotime($d)), array_keys($dailyCounts))); ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode(array_values($dailyCounts)); ?>,
                    borderColor: CHART_COLORS.caramel,
                    backgroundColor: 'rgba(193, 135, 63, 0.15)',
                    borderWidth: 2.5,
                    pointBackgroundColor: CHART_COLORS.caramelDark,
                    pointRadius: 3,
                    tension: 0.35,
                    fill: true
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: CHART_COLORS.cocoaSoft } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: CHART_COLORS.cocoaSoft }, grid: { color: CHART_COLORS.grid } }
                }
            }
        });

        // ---- Orders by status ----
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_values($statusLabels)); ?>,
                datasets: [{
                    data: [
                        <?php echo $stats['new']; ?>,
                        <?php echo $stats['contacted']; ?>,
                        <?php echo $stats['completed']; ?>,
                        <?php echo $stats['cancelled']; ?>
                    ],
                    backgroundColor: [CHART_COLORS.caramelLight, CHART_COLORS.caramel, CHART_COLORS.green, CHART_COLORS.red],
                    borderColor: '#FFFDF9',
                    borderWidth: 2
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { color: CHART_COLORS.cocoa, boxWidth: 12, padding: 14 } } }
            }
        });

        // ---- Orders by size ----
        new Chart(document.getElementById('sizeChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($sizeCounts)); ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode(array_values($sizeCounts)); ?>,
                    backgroundColor: CHART_COLORS.crumb,
                    borderRadius: 6,
                    maxBarThickness: 46
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: CHART_COLORS.cocoaSoft } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: CHART_COLORS.cocoaSoft }, grid: { color: CHART_COLORS.grid } }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>