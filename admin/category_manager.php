<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = $_POST['name'];
        $sort_order = (int) $_POST['sort_order'];

        $image_path = 'assets/images/placeholder.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $filename = time() . '_' . $_FILES['image']['name'];
            $target_dir = '../uploads/';
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename);
            $image_path = 'uploads/' . $filename;
        }

        $stmt = $pdo->prepare("INSERT INTO categories (name, image_path, sort_order) VALUES (?, ?, ?)");
        $stmt->execute([$name, $image_path, $sort_order]);
    } elseif (isset($_POST['delete_category'])) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$_POST['category_id']]);
    }
    header('Location: category_manager.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order ASC, id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Category Manager</title>
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
                <li><a href="category_manager.php" class="active">Categories</a></li>
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
                <h1>Category Manager</h1>
            </div>

            <div class="card">
                <h3>Add New Category</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-top:20px;">
                    <div style="display:flex; gap:20px;">
                        <div class="form-group" style="flex:1;">
                            <label>Category Name</label>
                            <input type="text" name="name" placeholder="e.g. Dogs" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Image</label>
                            <input type="file" name="image" accept="image/*">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" value="0" required>
                        </div>
                    </div>
                    <button type="submit" name="add_category" class="btn-primary">Add Category</button>
                </form>
            </div>

            <div class="card">
                <h3>Current Categories</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Sort Order</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><img src="../<?= htmlspecialchars($cat['image_path']) ?>" width="60" height="60"
                                        style="object-fit:cover; border-radius:10px;"></td>
                                <td><?= htmlspecialchars($cat['name']) ?></td>
                                <td><?= $cat['sort_order'] ?></td>
                                <td>
                                    <a href="edit_category.php?id=<?= $cat['id'] ?>" class="btn-primary"
                                        style="padding: 6px 12px; text-decoration: none; font-size: 14px; border-radius: 5px; margin-right: 5px;">Edit</a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?');">
                                        <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                        <button type="submit" name="delete_category" class="btn-danger">Delete</button>
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