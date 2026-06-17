<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: category_manager.php');
    exit;
}

$category_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    header('Location: category_manager.php');
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_category'])) {
        $name = $_POST['name'];
        $sort_order = (int) $_POST['sort_order'];
        $old_name = $category['name'];

        $image_path = $category['image_path'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $filename = time() . '_' . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $filename);
            $image_path = 'uploads/' . $filename;
            
            // Optionally delete the old image file if it wasn't a placeholder
            if ($category['image_path'] && $category['image_path'] !== 'assets/images/placeholder.jpg') {
                $old_file = '../' . $category['image_path'];
                if (file_exists($old_file) && is_file($old_file)) {
                    unlink($old_file);
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE categories SET name=?, image_path=?, sort_order=? WHERE id=?");
        $stmt->execute([$name, $image_path, $sort_order, $category_id]);

        // If the category name has changed, update matching products category name as well
        if ($name !== $old_name) {
            $stmt_products = $pdo->prepare("UPDATE products SET category = ? WHERE category = ?");
            $stmt_products->execute([$name, $old_name]);
        }

        header('Location: category_manager.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
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
                <li><a href="review_manager.php">Reviews</a></li>
                <li><a href="customizations.php">Customizations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header-top">
                <h1>Edit Category #<?= $category['id'] ?></h1>
                <a href="category_manager.php" class="btn-primary" style="text-decoration:none;">Back to Categories</a>
            </div>

            <div class="card">
                <h3>Update Category Details</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-top:20px;">
                    <div style="display:flex; gap:20px;">
                        <div class="form-group" style="flex:2;">
                            <label>Category Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Sort Order</label>
                            <input type="number" name="sort_order" value="<?= htmlspecialchars($category['sort_order']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 10px;">
                        <label>Current Image</label>
                        <div style="margin-bottom: 15px;">
                            <img src="../<?= htmlspecialchars($category['image_path']) ?>" 
                                 style="width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd;">
                        </div>
                        <label>Upload New Image (Optional)</label>
                        <input type="file" name="image" accept="image/*" style="border: 1px dashed #d6a86c; background: #fffdf9;">
                    </div>

                    <button type="submit" name="update_category" class="btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
