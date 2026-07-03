<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

// Fetch low stock items
$low_stock_stmt = $pdo->query("SELECT * FROM products WHERE stock_quantity <= 5 ORDER BY stock_quantity ASC");
$low_stock_products = $low_stock_stmt->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Archipaws Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
</head>

<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>🐶 ARCHIPAWS Pro</h2>
            <ul>
                <li><a href="index.php" class="active">Dashboard</a></li>
                <li><a href="hero_manager.php">Hero Slider</a></li>
                <li><a href="category_manager.php">Categories</a></li>
                <li><a href="product_manager.php">Products</a></li>
                <li><a href="deal_manager.php">Deal Of The Day</a></li>
                <li><a href="testimonial_manager.php">Testimonials</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="sales_report.php">Sales Report</a></li>
                <li><a href="review_manager.php">Reviews</a></li>
                <li><a href="customizations.php">Customizations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header-top">
                <h1>Overview</h1>
            </div>

            <?php if (!empty($low_stock_products)): ?>
                <div class="card" style="border-left: 5px solid #ff3b30; background: #fff8f8;">
                    <h3 style="color: #ff3b30; display: flex; align-items: center; gap: 8px;">
                        ⚠️ Low Stock Alerts (<?= count($low_stock_products) ?>)
                    </h3>
                    <p style="color: #666; margin-top: 5px; font-size: 14px;">The following products are running low on stock or out of stock:</p>
                    <table style="margin-top: 15px;">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Stock Qty</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock_products as $lp): ?>
                                <?php 
                                    $badge_color = $lp['stock_quantity'] <= 0 ? '#ff3b30' : '#ff9500';
                                    $bg_color = $lp['stock_quantity'] <= 0 ? '#ffeeee' : '#fff8e5';
                                    $status_label = $lp['stock_quantity'] <= 0 ? 'Out of Stock' : 'Low Stock';
                                ?>
                                <tr>
                                    <td style="font-weight: 600;"><?= htmlspecialchars($lp['title']) ?></td>
                                    <td><?= htmlspecialchars($lp['category']) ?></td>
                                    <td style="font-weight: 700; color: <?= $badge_color ?>;"><?= $lp['stock_quantity'] ?></td>
                                    <td>
                                        <span style="display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; color: <?= $badge_color ?>; background: <?= $bg_color ?>;">
                                            <?= $status_label ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_product.php?id=<?= $lp['id'] ?>" class="btn-primary" 
                                           style="padding: 5px 10px; font-size: 13px; text-decoration: none; border-radius: 6px;">Update Stock</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="card">
                <h3>Welcome to Archipaws Admin Pro</h3>
                <p style="color:#666; margin-top:10px;">Select a module from the left menu to manage the content of your
                    site.</p>
            </div>
        </div>
    </div>
</body>

</html>