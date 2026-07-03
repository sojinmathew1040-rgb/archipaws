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
        $discount_rate = (float)$_POST['discount_rate'];

        try {
            $pdo->beginTransaction();

            // 1. Restore the previous active deal product price if there was one
            $stmt = $pdo->query("SELECT * FROM deal_of_the_day LIMIT 1");
            $old_deal = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($old_deal) {
                $restoreStmt = $pdo->prepare("UPDATE products SET price = ?, old_price = ? WHERE id = ?");
                $restoreStmt->execute([$old_deal['original_price'], $old_deal['original_old_price'], $old_deal['product_id']]);
                $pdo->exec("DELETE FROM deal_of_the_day");
            }

            // 2. Fetch target product details
            $prodStmt = $pdo->prepare("SELECT price, old_price FROM products WHERE id = ?");
            $prodStmt->execute([$product_id]);
            $product = $prodStmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                $original_price = (float)$product['price'];
                $original_old_price = $product['old_price'] !== null ? (float)$product['old_price'] : null;

                // Calculate discounted price
                $new_price = max(0.01, $original_price - $discount_rate);

                // Update product table with deal price
                $updateProd = $pdo->prepare("UPDATE products SET price = ?, old_price = ? WHERE id = ?");
                $updateProd->execute([$new_price, $original_price, $product_id]);

                // Record deal in deal_of_the_day
                $insertDeal = $pdo->prepare("INSERT INTO deal_of_the_day (product_id, end_time, original_price, original_old_price, discount_rate) VALUES (?, ?, ?, ?, ?)");
                $insertDeal->execute([$product_id, $end_time, $original_price, $original_old_price, $discount_rate]);
            }

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['deal_error'] = "Failed to set deal: " . $e->getMessage();
        }
    } elseif (isset($_POST['remove_deal'])) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->query("SELECT * FROM deal_of_the_day LIMIT 1");
            $deal = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($deal) {
                // Restore original product pricing
                $restoreStmt = $pdo->prepare("UPDATE products SET price = ?, old_price = ? WHERE id = ?");
                $restoreStmt->execute([$deal['original_price'], $deal['original_old_price'], $deal['product_id']]);
                
                $pdo->exec("DELETE FROM deal_of_the_day");
            }

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['deal_error'] = "Failed to remove deal: " . $e->getMessage();
        }
    }
    header('Location: deal_manager.php');
    exit;
}

$current_deal = $pdo->query("SELECT d.*, p.title as product_title, p.price as current_price, p.old_price as product_old_price FROM deal_of_the_day d JOIN products p ON d.product_id = p.id LIMIT 1")->fetch();

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

            <?php if (isset($_SESSION['deal_error'])): ?>
                <div style="background:#ff3b30; color:#fff; padding:15px; border-radius:12px; margin-bottom:20px; font-weight:600;">
                    <?= $_SESSION['deal_error']; unset($_SESSION['deal_error']); ?>
                </div>
            <?php endif; ?>

            <div class="card" style="margin-bottom: 20px; background:#fff8e5; border-left: 5px solid #ffbc00;">
                <h3 style="color:#d69b00; margin-bottom: 15px;">Current Active Deal</h3>
                <?php if ($current_deal): ?>
                    <p><strong>Product:</strong> <?= htmlspecialchars($current_deal['product_title']) ?></p>
                    <p><strong>Original Price:</strong> ₹<?= number_format((float) $current_deal['original_price'], 2) ?></p>
                    <p><strong>Discount Applied:</strong> -₹<?= number_format((float) $current_deal['discount_rate'], 2) ?> (Deducted from actual price)</p>
                    <p><strong>Active Deal Price:</strong> <span style="font-weight:700; color:#ff3b30;">₹<?= number_format((float) $current_deal['current_price'], 2) ?></span></p>
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
                    <div style="display:flex; gap:20px; flex-wrap: wrap; margin-bottom: 15px;">
                        <div class="form-group" style="flex:2; min-width:250px;">
                            <label>Select Product</label>
                            <select name="product_id" required
                                style="width:100%; border:1px solid #ddd; padding:15px; border-radius:10px; background:#fff; height:50px; font-family: inherit;">
                                <option value="">-- Choose Product --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= htmlspecialchars($p['title']) ?> (₹<?= number_format((float) $p['price'], 2) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1; min-width:180px;">
                            <label>Discount Rate (₹ off)</label>
                            <input type="number" step="0.01" name="discount_rate" placeholder="e.g. 150.00" min="0" required
                                style="width:100%; border:1px solid #ddd; padding:15px; border-radius:10px; box-sizing:border-box; height:50px; font-family: inherit;">
                        </div>
                        <div class="form-group" style="flex:1; min-width:200px;">
                            <label>Deal End Time</label>
                            <input type="datetime-local" name="end_time" required
                                style="width:100%; border:1px solid #ddd; padding:15px; border-radius:10px; box-sizing:border-box; height:50px; font-family: inherit;">
                        </div>
                    </div>
                    <p style="font-size:13px; color:#888; margin-bottom:15px;">Setting a new deal will replace any
                        existing active deal. The selected discount rate will be subtracted from the actual price of the product and the original price will show as the old crossed-out price automatically.</p>
                    <button type="submit" name="set_deal" class="btn-primary">Set Deal of the Day</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>