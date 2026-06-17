<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_testimonial'])) {
        $customer_name = $_POST['customer_name'];
        $quote = $_POST['quote'];
        $rating = (int) $_POST['rating'];

        $image_path = 'assets/images/user-placeholder.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $filename = time() . '_' . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $filename);
            $image_path = 'uploads/' . $filename;
        }

        $stmt = $pdo->prepare("INSERT INTO testimonials (customer_name, quote, rating, image_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$customer_name, $quote, $rating, $image_path]);
    } elseif (isset($_POST['delete_testimonial'])) {
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$_POST['testimonial_id']]);
    }
    header('Location: testimonial_manager.php');
    exit;
}

$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Testimonial Manager</title>
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
                <li><a href="deal_manager.php">Deal Of The Day</a></li>
                <li><a href="testimonial_manager.php" class="active">Testimonials</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="review_manager.php">Reviews</a></li>
                <li><a href="customizations.php">Customizations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header-top">
                <h1>Testimonial Manager</h1>
            </div>

            <div class="card">
                <h3>Add New Testimonial</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-top:20px;">
                    <div style="display:flex; gap:20px;">
                        <div class="form-group" style="flex:1;">
                            <label>Customer Name</label>
                            <input type="text" name="customer_name" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Rating (1-5)</label>
                            <input type="number" name="rating" min="1" max="5" value="5" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Customer Image</label>
                            <input type="file" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Quote</label>
                        <textarea name="quote" rows="3" required
                            style="width:100%; border:1px solid #ddd; border-radius:10px; padding:15px;"></textarea>
                    </div>
                    <button type="submit" name="add_testimonial" class="btn-primary">Add Testimonial</button>
                </form>
            </div>

            <div class="card">
                <h3>Current Testimonials</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Quote</th>
                            <th>Rating</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($testimonials as $t): ?>
                            <tr>
                                <td><img src="../<?= htmlspecialchars($t['image_path'] ?? 'assets/images/user-placeholder.jpg') ?>"
                                        width="50" height="50" style="border-radius:50%; object-fit:cover;"></td>
                                <td><?= htmlspecialchars($t['customer_name']) ?></td>
                                <td style="max-width:300px; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">
                                    <?= htmlspecialchars($t['quote']) ?></td>
                                <td style="color:#f5b301;"><?= str_repeat('★', $t['rating']) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="testimonial_id" value="<?= $t['id'] ?>">
                                        <button type="submit" name="delete_testimonial" class="btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>