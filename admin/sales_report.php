<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Get filter parameters
$group_by = isset($_GET['group_by']) ? $_GET['group_by'] : 'monthly';
if (!in_array($group_by, ['daily', 'monthly', 'yearly'])) {
    $group_by = 'monthly';
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'exclude_cancelled';

// Set default date range if empty
if (empty($start_date)) {
    if ($group_by === 'daily') {
        $start_date = date('Y-m-d', strtotime('-30 days'));
    } elseif ($group_by === 'monthly') {
        $start_date = date('Y-m-d', strtotime('-12 months'));
    } else {
        $start_date = date('Y-m-d', strtotime('-5 years'));
    }
}
if (empty($end_date)) {
    $end_date = date('Y-m-d');
}

// Build Query Conditions
$conditions = [];
$params = [];

if (!empty($start_date)) {
    $conditions[] = "created_at >= :start_date";
    // Append 00:00:00 for timestamp completeness if it is standard date
    $params['start_date'] = $start_date . " 00:00:00";
}
if (!empty($end_date)) {
    $conditions[] = "created_at <= :end_date";
    // Append 23:59:59
    $params['end_date'] = $end_date . " 23:59:59";
}

if ($status_filter === 'exclude_cancelled') {
    $conditions[] = "status != 'Cancelled'";
} elseif ($status_filter !== 'all') {
    $conditions[] = "status = :status";
    $params['status'] = $status_filter;
}

$where_clause = "";
if (count($conditions) > 0) {
    $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// 1. Fetch KPI Metrics
$kpi_query = "SELECT 
    COALESCE(SUM(total_price), 0) as total_revenue,
    COUNT(id) as total_orders,
    COALESCE(AVG(total_price), 0) as avg_order_value,
    COUNT(DISTINCT email) as unique_customers
FROM orders 
$where_clause";

$stmt = $pdo->prepare($kpi_query);
$stmt->execute($params);
$kpis = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. Fetch Order Status distribution (ignoring status filter to show full picture, but keeping date range)
$status_conditions = [];
$status_params = [];
if (!empty($start_date)) {
    $status_conditions[] = "created_at >= :start_date";
    $status_params['start_date'] = $start_date . " 00:00:00";
}
if (!empty($end_date)) {
    $status_conditions[] = "created_at <= :end_date";
    $status_params['end_date'] = $end_date . " 23:59:59";
}
$status_where = count($status_conditions) > 0 ? "WHERE " . implode(" AND ", $status_conditions) : "";
$status_query = "SELECT status, COUNT(*) as count, COALESCE(SUM(total_price), 0) as total_sales FROM orders $status_where GROUP BY status";
$stmt_status = $pdo->prepare($status_query);
$stmt_status->execute($status_params);
$status_data = $stmt_status->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch Time-Series Trend Data
if ($group_by === 'daily') {
    $date_expr = "DATE(created_at)";
    $date_format = "%Y-%m-%d";
} elseif ($group_by === 'monthly') {
    $date_expr = "DATE_FORMAT(created_at, '%Y-%m')";
    $date_format = "%Y-%m";
} else {
    $date_expr = "DATE_FORMAT(created_at, '%Y')";
    $date_format = "%Y";
}

$trend_query = "SELECT 
    $date_expr as period,
    COALESCE(SUM(total_price), 0) as sales,
    COUNT(id) as orders_count,
    COALESCE(AVG(total_price), 0) as average_value
FROM orders
$where_clause
GROUP BY period
ORDER BY period ASC";

$stmt_trend = $pdo->prepare($trend_query);
$stmt_trend->execute($params);
$trend_data = $stmt_trend->fetchAll(PDO::FETCH_ASSOC);

// Format trend data for Chart.js
$labels = [];
$sales_points = [];
$orders_points = [];

foreach ($trend_data as $row) {
    if ($group_by === 'daily') {
        $labels[] = date('M j, Y', strtotime($row['period']));
    } elseif ($group_by === 'monthly') {
        $labels[] = date('F Y', strtotime($row['period'] . '-01'));
    } else {
        $labels[] = $row['period'];
    }
    $sales_points[] = (float)$row['sales'];
    $orders_points[] = (int)$row['orders_count'];
}

// Format status data for Chart.js
$status_labels = [];
$status_counts = [];
$status_colors = [];
$status_color_map = [
    'Pending' => '#d6a86c',
    'Received' => '#5ac8fa',
    'Processing' => '#5856d6',
    'Shipped' => '#34c759',
    'Delivered' => '#248a3d',
    'Cancelled' => '#ff3b30'
];

foreach ($status_data as $s) {
    $status_labels[] = $s['status'];
    $status_counts[] = (int)$s['count'];
    $status_colors[] = isset($status_color_map[$s['status']]) ? $status_color_map[$s['status']] : '#8e8e93';
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports - Archipaws Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .filters-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
        }

        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: flex-end;
        }

        .filter-btn-group {
            display: flex;
            gap: 10px;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .kpi-card {
            background: #fff;
            padding: 24px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            border-left: 5px solid #d6a86c;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
        }

        .kpi-card.sales { border-left-color: #248a3d; }
        .kpi-card.orders { border-left-color: #d6a86c; }
        .kpi-card.aov { border-left-color: #5856d6; }
        .kpi-card.customers { border-left-color: #ff9500; }

        .kpi-title {
            font-size: 14px;
            color: #8e8e93;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .kpi-value {
            font-size: 28px;
            font-weight: 700;
            color: #1d1d1f;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 1024px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        .chart-card {
            background: #fff;
            padding: 24px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            min-height: 380px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #1d1d1f;
        }

        .table-card {
            background: #fff;
            padding: 24px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            margin-bottom: 30px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-outline {
            padding: 10px 18px;
            border: 1px solid #ddd;
            background: #fff;
            color: #1d1d1f;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline:hover {
            border-color: #1d1d1f;
            background: #f5f5f7;
        }

        .print-header {
            display: none;
        }

        /* Printable stylesheet overrides */
        @media print {
            body {
                background: #fff;
                color: #000;
            }

            .print-header {
                display: flex !important;
                justify-content: space-between;
                align-items: center;
                border-bottom: 2px solid #d6a86c;
                padding-bottom: 15px;
                margin-bottom: 30px;
            }

            .print-header .logo {
                font-size: 26px;
                font-weight: 700;
                color: #d6a86c;
                text-decoration: none;
                display: flex;
                flex-direction: column;
                line-height: 1.2;
                align-items: flex-start;
            }

            .print-header .logo span {
                color: #1d1d1f;
                font-size: 24px;
                font-weight: 700;
            }

            .print-header .logo small {
                display: block;
                font-size: 12px;
                color: #8e8e93;
                font-weight: 500;
                margin-top: -2px;
            }

            .print-header .report-title {
                text-align: right;
            }

            .print-header .report-title h2 {
                font-size: 20px;
                font-weight: 700;
                color: #1d1d1f;
                margin: 0 0 5px 0;
            }

            .print-header .report-title p {
                font-size: 12px;
                color: #666;
                margin: 2px 0;
            }

            .sidebar, 
            .filters-wrapper, 
            .action-buttons,
            .header-top {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .kpi-card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }

            .chart-card,
            .table-card {
                box-shadow: none !important;
                border: 1px solid #eee !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>🐶 ARCHIPAWS Pro</h2>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="hero_manager.php">Hero Slider</a></li>
                <li><a href="category_manager.php">Categories</a></li>
                <li><a href="product_manager.php">Products</a></li>
                <li><a href="deal_manager.php">Deal Of The Day</a></li>
                <li><a href="testimonial_manager.php">Testimonials</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="sales_report.php" class="active">Sales Report</a></li>
                <li><a href="review_manager.php">Reviews</a></li>
                <li><a href="customizations.php">Customizations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Print Header with Logo -->
            <div class="print-header">
                <div class="logo">
                    🐶 <span>archipaws</span>
                    <small>Pet Shop</small>
                </div>
                <div class="report-title">
                    <h2>Sales Analytics Report</h2>
                    <p class="report-period">Period: <?= htmlspecialchars(ucfirst($group_by)) ?> Breakdown (<?= htmlspecialchars(date('M j, Y', strtotime($start_date))) ?> - <?= htmlspecialchars(date('M j, Y', strtotime($end_date))) ?>)</p>
                    <p class="report-generated">Generated: <?= date('F j, Y, g:i A') ?></p>
                </div>
            </div>

            <div class="header-top">
                <h1>Sales Analytics</h1>
                <div class="action-buttons">
                    <button onclick="window.print()" class="btn-outline">🖨️ Print Report</button>
                    <button onclick="exportToCSV()" class="btn-outline">📥 Export CSV</button>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filters-wrapper">
                <form method="GET" action="sales_report.php" class="filters-form">
                    <div class="form-group">
                        <label for="group_by">View By</label>
                        <select name="group_by" id="group_by" onchange="this.form.submit()">
                            <option value="daily" <?= $group_by === 'daily' ? 'selected' : '' ?>>Daily</option>
                            <option value="monthly" <?= $group_by === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="yearly" <?= $group_by === 'yearly' ? 'selected' : '' ?>>Yearly</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars(date('Y-m-d', strtotime($start_date))) ?>">
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars(date('Y-m-d', strtotime($end_date))) ?>">
                    </div>

                    <div class="form-group">
                        <label for="status">Order Status</label>
                        <select name="status" id="status">
                            <option value="exclude_cancelled" <?= $status_filter === 'exclude_cancelled' ? 'selected' : '' ?>>Active (Excl. Cancelled)</option>
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                            <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending Only</option>
                            <option value="Received" <?= $status_filter === 'Received' ? 'selected' : '' ?>>Received Only</option>
                            <option value="Processing" <?= $status_filter === 'Processing' ? 'selected' : '' ?>>Processing Only</option>
                            <option value="Shipped" <?= $status_filter === 'Shipped' ? 'selected' : '' ?>>Shipped Only</option>
                            <option value="Delivered" <?= $status_filter === 'Delivered' ? 'selected' : '' ?>>Delivered Only</option>
                            <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled Only</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">Apply Filters</button>
                    </div>
                </form>
            </div>

            <!-- KPI Metric Cards -->
            <div class="kpi-grid">
                <div class="kpi-card sales">
                    <div class="kpi-title">Total Revenue</div>
                    <div class="kpi-value">₹<?= number_format($kpis['total_revenue'], 2) ?></div>
                </div>
                <div class="kpi-card orders">
                    <div class="kpi-title">Total Orders</div>
                    <div class="kpi-value"><?= number_format($kpis['total_orders']) ?></div>
                </div>
                <div class="kpi-card aov">
                    <div class="kpi-title">Average Order Value</div>
                    <div class="kpi-value">₹<?= number_format($kpis['avg_order_value'], 2) ?></div>
                </div>
                <div class="kpi-card customers">
                    <div class="kpi-title">Unique Customers</div>
                    <div class="kpi-value"><?= number_format($kpis['unique_customers']) ?></div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid">
                <!-- Trend Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Sales & Order Trends</h3>
                        <span style="font-size:12px; color:#8e8e93; font-weight: 600; text-transform: uppercase;">
                            <?= htmlspecialchars(ucfirst($group_by)) ?> Breakdown
                        </span>
                    </div>
                    <div style="position: relative; height: 320px; width: 100%;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Status Distribution Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Order Statuses</h3>
                    </div>
                    <div style="position: relative; height: 300px; width: 100%; display: flex; align-items: center; justify-content: center;">
                        <?php if (empty($status_data)): ?>
                            <p style="color: #8e8e93; font-size: 14px;">No order status data available.</p>
                        <?php else: ?>
                            <canvas id="statusChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Data Table Section -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Detailed Report Table</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table id="salesTable">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Total Revenue</th>
                                <th>Total Orders</th>
                                <th>Average Order Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($trend_data)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; color: #8e8e93;">No data matches the selected filters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_reverse($trend_data) as $row): ?>
                                    <?php
                                    if ($group_by === 'daily') {
                                        $display_period = date('M j, Y', strtotime($row['period']));
                                    } elseif ($group_by === 'monthly') {
                                        $display_period = date('F Y', strtotime($row['period'] . '-01'));
                                    } else {
                                        $display_period = $row['period'];
                                    }
                                    ?>
                                    <tr>
                                        <td style="font-weight: 600; color: #1d1d1f;"><?= htmlspecialchars($display_period) ?></td>
                                        <td style="font-weight: 700; color: #248a3d;">₹<?= number_format($row['sales'], 2) ?></td>
                                        <td><?= number_format($row['orders_count']) ?></td>
                                        <td>₹<?= number_format($row['average_value'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Chart rendering logic -->
    <script>
        // 1. Trend Chart (Double Y-Axis Line & Bar Chart)
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const labels = <?= json_encode($labels) ?>;
        const salesData = <?= json_encode($sales_points) ?>;
        const ordersData = <?= json_encode($orders_points) ?>;

        // Create colorful gradient for line fill
        const salesGradient = trendCtx.createLinearGradient(0, 0, 0, 320);
        salesGradient.addColorStop(0, 'rgba(88, 86, 214, 0.4)');   // Vibrant Purple
        salesGradient.addColorStop(0.5, 'rgba(255, 45, 85, 0.15)'); // Hot Pink
        salesGradient.addColorStop(1, 'rgba(255, 149, 0, 0.02)');   // Warm Orange

        // Create colorful gradient for line stroke
        const salesLineGradient = trendCtx.createLinearGradient(0, 0, 800, 0);
        salesLineGradient.addColorStop(0, '#5856d6');   // Purple
        salesLineGradient.addColorStop(0.5, '#ff2d55'); // Pink
        salesLineGradient.addColorStop(1, '#ff9500');   // Orange

        // Vibrant rotating color palette for individual bars
        const colorsPalette = [
            'rgba(54, 162, 235, 0.7)',   // Ocean Blue
            'rgba(255, 99, 132, 0.7)',   // Coral Red
            'rgba(75, 192, 192, 0.7)',   // Turquoise/Teal
            'rgba(153, 102, 255, 0.7)',  // Amethyst Violet
            'rgba(255, 159, 64, 0.7)',   // Tangerine Orange
            'rgba(46, 204, 113, 0.7)',   // Emerald Green
            'rgba(255, 206, 86, 0.7)',   // Sunflower Yellow
            'rgba(231, 76, 60, 0.7)'     // Alizarin Red
        ];
        const borderPalette = [
            '#36a2eb',
            '#ff6384',
            '#4bc0c0',
            '#9966ff',
            '#ff9f40',
            '#2ecc71',
            '#ffcd56',
            '#e74c3c'
        ];

        const barBackgrounds = ordersData.map((_, index) => colorsPalette[index % colorsPalette.length]);
        const barBorders = ordersData.map((_, index) => borderPalette[index % borderPalette.length]);

        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue (₹)',
                        data: salesData,
                        borderColor: salesLineGradient,
                        backgroundColor: salesGradient,
                        borderWidth: 4,
                        pointBackgroundColor: '#5856d6',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#5856d6',
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        fill: true,
                        yAxisID: 'ySales',
                        tension: 0.3
                    },
                    {
                        label: 'Orders Count',
                        data: ordersData,
                        type: 'bar',
                        backgroundColor: barBackgrounds,
                        hoverBackgroundColor: barBorders,
                        borderColor: barBorders,
                        borderWidth: 1.5,
                        borderRadius: 6,
                        yAxisID: 'yOrders',
                        barThickness: 'flex',
                        maxBarThickness: 30
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Quicksand',
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        titleFont: { family: 'Quicksand', weight: '700' },
                        bodyFont: { family: 'Quicksand' }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Quicksand',
                                weight: '500'
                            }
                        }
                    },
                    ySales: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (₹)',
                            font: { family: 'Quicksand', weight: '600' }
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            },
                            font: { family: 'Quicksand' }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    yOrders: {
                        type: 'linear',
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Orders Count',
                            font: { family: 'Quicksand', weight: '600' }
                        },
                        ticks: {
                            stepSize: 1,
                            precision: 0,
                            font: { family: 'Quicksand' }
                        },
                        grid: {
                            drawOnChartArea: false // prevent grid lines from overlapping
                        }
                    }
                }
            }
        });

        // 2. Order Status distribution chart
        <?php if (!empty($status_data)): ?>
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($status_labels) ?>,
                datasets: [{
                    data: <?= json_encode($status_counts) ?>,
                    backgroundColor: <?= json_encode($status_colors) ?>,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: {
                                family: 'Quicksand',
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const val = context.raw;
                                const pct = ((val / total) * 100).toFixed(1);
                                return ` ${context.label}: ${val} orders (${pct}%)`;
                            }
                        },
                        titleFont: { family: 'Quicksand', weight: '700' },
                        bodyFont: { family: 'Quicksand' }
                    }
                },
                cutout: '70%'
            }
        });
        <?php endif; ?>

        // 3. Export to CSV Function
        function exportToCSV() {
            const table = document.getElementById("salesTable");
            let csvContent = "";
            
            // Loop through all table rows
            for (let i = 0; i < table.rows.length; i++) {
                let row = table.rows[i];
                let rowData = [];
                for (let j = 0; j < row.cells.length; j++) {
                    let cellText = row.cells[j].innerText.trim();
                    // Remove currency symbols, commas, and handle spacing
                    cellText = cellText.replace(/[₹,]/g, "");
                    // Wrap with quotes if containing space or comma
                    if (cellText.indexOf(" ") >= 0 || cellText.indexOf(",") >= 0) {
                        cellText = `"${cellText}"`;
                    }
                    rowData.push(cellText);
                }
                csvContent += rowData.join(",") + "\r\n";
            }
            
            // Trigger file download
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            
            // Generate filename based on view and dates
            const viewType = document.getElementById("group_by").value;
            const startDate = document.getElementById("start_date").value || "all";
            const endDate = document.getElementById("end_date").value || "all";
            link.setAttribute("download", `sales_report_${viewType}_${startDate}_to_${endDate}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>

</html>
