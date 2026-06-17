<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['set_deal'])) {
        $product_id = $_POST['product_id'];
        $end_time = $_POST['end_time'];

        // Clear existing
        $pdo->exec("DELETE FROM deal_of_the_day");

        // Insert new
        $stmt = $pdo->prepare("INSERT INTO deal_of_the_day (product_id, end_time) VALUES (?, ?)");
        $stmt->execute([$product_id, $end_time]);
    } elseif (isset($_POST['remove_deal'])) {
        $pdo->exec("DELETE FROM deal_of_the_day");
    }
    header('Location: deal_manager.php');
    exit;
}

$current_deal = $pdo->query("SELECT d.*, p.title as product_title, p.price as current_price, p.old_price FROM deal_of_the_day d JOIN products p ON d.product_id = p.id LIMIT 1")->fetch();

$products = $pdo->query("SELECT id, title, price, old_price FROM products ORDER BY title ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Deal Of The Day</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
</head>

<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>🐶 ARCHIPAWS Pro</h2>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="hero_manager.php">Hero Slider</a></li>
                <li><a href="category_manager.php">Categories</a></li>
                <li><a href="product_manager.php">Products</a></li>
                <li><a href="deal_manager.php" class="active">Deal Of The Day</a></li>
                <li><a href="testimonial_manager.php">Testimonials</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="review_manager.php">Reviews</a></li>
                <li><a href="customizations.php">Customizations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header-top">
                <h1>Deal Of The Day Manager</h1>
            </div>

            <div class="card" style="margin-bottom: 20px; background:#fff8e5; border-left: 5px solid #ffbc00;">
                <h3 style="color:#d69b00; margin-bottom: 10px;">Current Active Deal</h3>
                <?php if ($current_deal): ?>
                    <p><strong>Product:</strong> <?= htmlspecialchars($current_deal['product_title']) ?></p>
                    <p><strong>Price:</strong> <span
                            style="text-decoration:line-through;color:#aaa;">₹<?= number_format((float) $current_deal['old_price'], 2) ?></span>
                        ₹<?= number_format((float) $current_deal['current_price'], 2) ?></p>
                    <p><strong>Ends At:</strong> <?= date('F j, Y, g:i a', strtotime($current_deal['end_time'])) ?></p>

                    <form method="POST" style="margin-top:15px;">
                        <button type="submit" name="remove_deal" class="btn-danger">Remove Active Deal</button>
                    </form>
                <?php else: ?>
                    <p style="color:#666;">No active deal at the moment.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Set A New Deal</h3>
                <form method="POST" style="margin-top:20px;">
                    <div style="display:flex; gap:20px;">
                        <div class="form-group" style="flex:1;">
                            <label>Select Product</label>
                            <select name="product_id" required
                                style="width:100%; border:1px solid #ddd; padding:15px; border-radius:10px;">
                                <option value="">-- Choose Product --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= htmlspecialchars($p['title']) ?> (₹<?= number_format((float) $p['price'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Deal End Time</label>
                            <input type="datetime-local" name="end_time" required>
                        </div>
                    </div>
                    <p style="font-size:13px; color:#888; margin-bottom:15px;">Setting a new deal will replace any
                        existing active deal. Ensure the product has an "Old Price" set in the Product Manager for the
                        sale effect to show.</p>
                    <button type="submit" name="set_deal" class="btn-primary">Set Deal of the Day</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>