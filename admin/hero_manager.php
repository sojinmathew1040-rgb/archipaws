<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if POST was truncated due to post_max_size limit
    if (empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $max_post_size = ini_get('post_max_size');
        $_SESSION['upload_error'] = "The file you uploaded is too large. The upload exceeded PHP's 'post_max_size' limit ($max_post_size). Please increase 'post_max_size' and 'upload_max_filesize' in your php.ini file.";
        header('Location: hero_manager.php');
        exit;
    }

    if (isset($_POST['add_slide'])) {
        $offer_text = '';
        $title_line1 = '';
        $title_line2 = '';
        $button_text = '';
        $button_link = $_POST['button_link'] ?? 'shop.php';
        $phone_number = $_POST['phone_number'] ?? '';
        $email_address = $_POST['email_address'] ?? '';

        $image_path = '';
        $upload_error = '';

        if (isset($_FILES['image'])) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'ogg', 'mov', 'avi'];
                $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($file_ext, $allowed_exts)) {
                    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['image']['name']);
                    if (move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $filename)) {
                        $image_path = 'uploads/' . $filename;
                    } else {
                        $upload_error = "Failed to save the uploaded file to disk.";
                    }
                } else {
                    $upload_error = "Invalid format. Allowed formats: " . implode(', ', $allowed_exts);
                }
            } elseif ($_FILES['image']['error'] === UPLOAD_ERR_INI_SIZE) {
                $max_size = ini_get('upload_max_filesize');
                $upload_error = "The uploaded file is too large. It exceeds PHP's 'upload_max_filesize' limit ($max_size) in php.ini.";
            } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $upload_error = "Upload error (code " . $_FILES['image']['error'] . ").";
            }
        }

        if ($upload_error) {
            $_SESSION['upload_error'] = $upload_error;
        } elseif (!empty($image_path)) {
            $stmt = $pdo->prepare("INSERT INTO hero_slides (offer_text, title_line1, title_line2, button_text, button_link, image_path, phone_number, email_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$offer_text, $title_line1, $title_line2, $button_text, $button_link, $image_path, $phone_number, $email_address]);
        }
    } elseif (isset($_POST['delete_slide'])) {
        $stmt = $pdo->prepare("DELETE FROM hero_slides WHERE id = ?");
        $stmt->execute([$_POST['slide_id']]);
    }
    header('Location: hero_manager.php');
    exit;
}

$slides = $pdo->query("SELECT * FROM hero_slides ORDER BY sort_order ASC, id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Hero Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
</head>

<body>
    <div class="admin-container">
        <div class="sidebar">
            <h2>🐶 ARCHIPAWS Pro</h2>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="hero_manager.php" class="active">Hero Slider</a></li>
                <li><a href="category_manager.php">Categories</a></li>
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
                <h1>Hero Slider Manager</h1>
            </div>

            <?php if (isset($_SESSION['upload_error'])): ?>
                <div style="background:#ff3b30; color:#fff; padding:15px; border-radius:12px; margin-bottom:20px; font-weight:600;">
                    <?= $_SESSION['upload_error']; unset($_SESSION['upload_error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <h3>Add New Slide</h3>
                <form method="POST" enctype="multipart/form-data" style="margin-top:20px;">
                    <input type="hidden" name="add_slide" value="1">
                    <div style="display:flex; gap:20px;">
                        <div class="form-group" style="flex:1;">
                            <label>Media File (Image or Video)</label>
                            <input type="file" name="image" required accept="image/*,video/*" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; box-sizing:border-box;">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Shop Page Link</label>
                            <input type="text" name="button_link" value="shop.php" placeholder="e.g. shop.php" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; box-sizing:border-box; height:40px;">
                        </div>
                    </div>
                    <div style="display:flex; gap:20px; margin-top:10px;">
                        <div class="form-group" style="flex:1;">
                            <label>Contact Phone Number (Optional)</label>
                            <input type="text" name="phone_number" placeholder="e.g. +919876543210" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; box-sizing:border-box; height:40px;">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label>Contact Email (Optional)</label>
                            <input type="email" name="email_address" placeholder="e.g. contact@archipaws.com" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:8px; box-sizing:border-box; height:40px;">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top:20px;">Add Slide</button>
                </form>
            </div>

            <div class="card">
                <h3>Current Slides</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Media Preview</th>
                            <th>Shop Link</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slides as $slide): ?>
                            <tr>
                                <td>
                                    <?php
                                    $ext = strtolower(pathinfo($slide['image_path'], PATHINFO_EXTENSION));
                                    $is_video = in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi']);
                                    if ($is_video):
                                    ?>
                                        <video src="../<?= htmlspecialchars($slide['image_path']) ?>" width="120" height="80" style="object-fit:cover; border-radius:8px;" autoplay muted loop playsinline></video>
                                    <?php else: ?>
                                        <img src="../<?= htmlspecialchars($slide['image_path']) ?>" width="120" height="80" style="object-fit:cover; border-radius:8px;">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($slide['button_link'] ?? 'shop.php') ?></td>
                                <td><?= htmlspecialchars($slide['phone_number'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($slide['email_address'] ?? '-') ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="slide_id" value="<?= $slide['id'] ?>">
                                        <button type="submit" name="delete_slide" class="btn-danger">Delete</button>
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