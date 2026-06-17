<?php
require_once 'db.php';
include 'header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo "<h2 style='text-align:center; margin:100px 0;'>Product not found!</h2>";
    include 'footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<h2 style='text-align:center; margin:100px 0;'>Product not found!</h2>";
    include 'footer.php';
    exit;
}

$img_stmt = $pdo->prepare("SELECT image_path, color_value FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$img_stmt->execute([$id]);
$images_raw = $img_stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($images_raw)) {
    $images_raw = [['image_path' => 'assets/images/16.jpeg', 'color_value' => null]];
}
$images = array_column($images_raw, 'image_path');

$stock_status_color = '#34c759';
$stock_bg = '#eefaf0';
if ($product['stock_quantity'] <= 0) {
    $stock_status_color = '#ff3b30';
    $stock_bg = '#ffeeee';
} elseif ($product['stock_quantity'] <= 5) {
    $stock_status_color = '#ff9500';
    $stock_bg = '#fff8e5';
}

$sort = $_GET['sort'] ?? 'newest';
$order_by = $sort === 'oldest' ? 'ASC' : 'DESC';
$rev_stmt = $pdo->prepare("SELECT r.*, u.name FROM product_reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at $order_by");
$rev_stmt->execute([$id]);
$reviews = $rev_stmt->fetchAll();
$current_user_id = $_SESSION['user_id'] ?? 0;

$user_name = '';
$user_email = '';
if ($current_user_id) {
    $u_stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $u_stmt->execute([$current_user_id]);
    $u_info = $u_stmt->fetch(PDO::FETCH_ASSOC);
    if ($u_info) {
        $user_name = $u_info['name'] ?? '';
        $user_email = $u_info['email'] ?? '';
    }
}

$spec_stmt = $pdo->prepare("SELECT * FROM product_specifications WHERE product_id = ? ORDER BY id ASC");
$spec_stmt->execute([$id]);
$product_specs = $spec_stmt->fetchAll();

$var_stmt = $pdo->prepare("SELECT * FROM product_variations WHERE product_id = ? ORDER BY id ASC");
$var_stmt->execute([$id]);
$product_vars = $var_stmt->fetchAll();

$unique_colors = [];
$unique_sizes = [];
$combinations = [];

foreach ($product_vars as $v) {
    if ($v['variation_name'] === 'Combination') {
        $parts = explode('/', $v['variation_value']);
        $color = trim($parts[0]);
        $size = isset($parts[1]) ? trim($parts[1]) : '';
        
        if ($color !== '') {
            $unique_colors[] = $color;
        }
        if ($size !== '') {
            $unique_sizes[] = $size;
        }
        
        $combinations[] = [
            'id' => $v['id'],
            'color' => $color,
            'size' => $size,
            'price_modifier' => (float)$v['price_modifier'],
            'stock_quantity' => (int)$v['stock_quantity'],
            'height' => $v['height'] ? (float)$v['height'] : null,
            'width' => $v['width'] ? (float)$v['width'] : null
        ];
    }
}
$unique_colors = array_values(array_unique($unique_colors));
$unique_sizes = array_values(array_unique($unique_sizes));
?>

<style>
    /* Apple-Level Pro style overrides for product.php */
    body {
        background: #fbfbfd;
    }

    .pro-product-wrapper {
        max-width: 1400px;
        margin: 40px auto 80px auto;
        padding: 0 40px;
        display: flex;
        gap: 80px;
        align-items: flex-start;
    }

    .pro-gallery {
        width: 55%;
        position: sticky;
        top: 100px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .pro-main-img {
        background: #fff;
        border-radius: 32px;
        padding: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        cursor: zoom-in;
        height: 600px;
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .pro-main-img img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .pro-thumbs-list {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .pro-thumbs-list::-webkit-scrollbar {
        height: 0;
    }

    .pro-thumb {
        width: 100px;
        height: 100px;
        background: #fff;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        border: 2px solid transparent;
        transition: all 0.3s ease;
        padding: 10px;
    }

    .pro-thumb img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .pro-thumb.active {
        border-color: #1d1d1f;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        transform: scale(1.05);
    }

    .pro-details {
        width: 45%;
        padding-top: 20px;
    }

    .pro-title {
        font-size: 48px;
        font-weight: 700;
        color: #1d1d1f;
        letter-spacing: -0.04em;
        line-height: 1.1;
        margin-bottom: 16px;
    }

    .pro-price {
        font-size: 32px;
        font-weight: 600;
        color: #1d1d1f;
        letter-spacing: -0.02em;
        margin-bottom: 30px;
    }

    .pro-old-price {
        font-size: 24px;
        color: #86868b;
        text-decoration: line-through;
        font-weight: 500;
        margin-right: 12px;
    }

    .pro-stock-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        border-radius: 980px;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 30px;
        background:
            <?= $stock_bg ?>
        ;
        color:
            <?= $stock_status_color ?>
        ;
        letter-spacing: 0.02em;
    }

    .pro-desc {
        font-size: 17px;
        line-height: 1.6;
        color: #86868b;
        letter-spacing: -0.01em;
        margin-bottom: 50px;
    }

    .pro-action-area {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .pro-qty {
        display: flex;
        align-items: center;
        background: #fff;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        border-radius: 980px;
        padding: 5px 20px;
        height: 56px;
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .pro-qty button {
        background: none;
        border: none;
        font-size: 24px;
        color: #1d1d1f;
        cursor: pointer;
        padding: 0 10px;
        height: 100%;
        display: flex;
        align-items: center;
    }

    .pro-qty button:hover {
        color: #d6a86c;
    }

    .pro-qty input {
        width: 40px;
        border: none;
        text-align: center;
        font-size: 19px;
        font-weight: 600;
        color: #1d1d1f;
        pointer-events: none;
    }

    .pro-add-btn {
        flex: 1;
        height: 56px;
        background: #1d1d1f;
        color: #fff;
        font-size: 17px;
        font-weight: 600;
        border-radius: 980px;
        border: none;
        cursor: pointer;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), background 0.3s;
    }

    .pro-add-btn:hover {
        transform: scale(0.98);
        background: #333336;
    }

    .pro-add-btn:disabled {
        background: #f5f5f7;
        color: #ccc;
        cursor: not-allowed;
        transform: none;
    }

    @media(max-width: 992px) {
        .pro-product-wrapper {
            flex-direction: column;
            padding: 0 20px;
            gap: 40px;
        }

        .pro-gallery,
        .pro-details {
            width: 100%;
            position: static;
        }

        .pro-title {
            font-size: 36px;
        }

        .pro-main-img {
            height: 400px;
            padding: 30px;
        }

        .pro-action-area {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
        }

        .pro-qty {
            justify-content: space-between;
        }

        .pro-add-btn {
            flex: none;
            width: 100%;
        }
    }

    /* Lightbox Pro */
    .lbx-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(29, 29, 31, 0.9);
        backdrop-filter: blur(20px);
        z-index: 10000;
    }

    .lbx-content {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        width: 100%;
        position: relative;
    }

    .lbx-img {
        max-width: 90%;
        max-height: 90vh;
        border-radius: 24px;
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5);
    }

    .lbx-close {
        position: absolute;
        top: 40px;
        right: 40px;
        color: #fff;
        font-size: 40px;
        cursor: pointer;
        opacity: 0.6;
        transition: 0.3s;
    }

    .lbx-close:hover {
        opacity: 1;
    }

    .lbx-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 60px;
        height: 60px;
        border-radius: 30px;
        border: none;
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        font-size: 24px;
        cursor: pointer;
        backdrop-filter: blur(10px);
        transition: 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lbx-nav:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-50%) scale(1.1);
    }

    .lbx-prev {
        left: 40px;
    }

    .lbx-next {
        right: 40px;
    }

    /* Reviews Pro */
    .pro-reviews-wrapper {
        max-width: 1400px;
        margin: 0 auto 80px auto;
        padding: 0 40px;
    }

    .pro-reviews-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 40px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding-bottom: 20px;
    }

    .pro-reviews-title {
        font-size: 32px;
        font-weight: 700;
        color: #1d1d1f;
    }

    .pro-reviews-filter select {
        padding: 10px 15px;
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        font-size: 15px;
        background: #fff;
        cursor: pointer;
    }

    .pro-review-card {
        background: #fff;
        border-radius: 24px;
        padding: 30px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .pro-review-head {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .pro-review-user {
        font-weight: 600;
        font-size: 17px;
        color: #1d1d1f;
    }

    .pro-review-date {
        font-size: 14px;
        color: #86868b;
    }

    .pro-review-rating {
        color: #d6a86c;
        margin-bottom: 15px;
    }

    .pro-review-text {
        font-size: 15px;
        color: #333;
        line-height: 1.5;
        margin-bottom: 15px;
    }

    .pro-review-photo {
        max-width: 200px;
        border-radius: 12px;
        cursor: pointer;
    }

    .pro-admin-reply {
        background: #f5f5f7;
        padding: 20px;
        border-radius: 16px;
        margin-top: 20px;
        border-left: 4px solid #1d1d1f;
    }

    .pro-admin-reply-label {
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 5px;
        color: #1d1d1f;
    }

    .pro-review-actions {
        margin-top: 15px;
        display: flex;
        gap: 10px;
    }

    .pro-btn-outline {
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #1d1d1f;
        background: transparent;
        color: #1d1d1f;
        font-size: 13px;
        cursor: pointer;
        transition: 0.3s;
    }

    .pro-btn-outline:hover {
        background: #1d1d1f;
        color: #fff;
    }

    .pro-btn-danger {
        border-color: #ff3b30;
        color: #ff3b30;
    }

    .pro-btn-danger:hover {
        background: #ff3b30;
        color: #fff;
    }

    .pro-review-form {
        background: #fff;
        border-radius: 24px;
        padding: 30px;
        margin-bottom: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .pro-review-form textarea {
        width: 100%;
        padding: 15px;
        border-radius: 12px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
        font-family: inherit;
        resize: vertical;
        min-height: 100px;
    }

    .pro-review-form select,
    .pro-review-form input[type="file"] {
        margin-bottom: 15px;
        padding: 10px;
    }

    .pro-btn-solid {
        padding: 12px 24px;
        border-radius: 980px;
        background: #1d1d1f;
        color: #fff;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }

    .pro-btn-solid:hover {
        background: #333;
    }

    @media(max-width: 992px) {
        .pro-reviews-wrapper {
            padding: 0 20px;
        }

        .pro-reviews-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
    }

    /* Premium Customization Section Styling */
    .pro-customization-wrapper {
        max-width: 1400px;
        margin: 0 auto 80px auto;
        padding: 0 40px;
    }

    .customization-card {
        background: #fff;
        border-radius: 32px;
        padding: 50px;
        display: flex;
        gap: 60px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.02);
        align-items: center;
        background: linear-gradient(135deg, #ffffff 0%, #fbfbfd 100%);
    }

    .customization-info {
        flex: 1.2;
    }

    .customization-info h2 {
        font-size: 32px;
        font-weight: 700;
        color: #1d1d1f;
        margin-bottom: 20px;
        letter-spacing: -0.02em;
    }

    .customization-info p {
        font-size: 16px;
        line-height: 1.6;
        color: #86868b;
        margin-bottom: 15px;
    }

    .customization-info .cust-subtext {
        font-size: 14px;
        color: #b0b0b5;
    }

    .cust-icon {
        font-size: 40px;
        margin-bottom: 20px;
        background: #f5f5f7;
        width: 70px;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .contact-details {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        border-top: 1px solid #eee;
        padding-top: 25px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 15px;
        color: #1d1d1f;
    }

    .c-icon {
        font-size: 18px;
    }

    .customization-form-container {
        flex: 1;
        background: #ffffff;
        padding: 40px;
        border-radius: 24px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .customization-form-container h3 {
        font-size: 20px;
        font-weight: 600;
        color: #1d1d1f;
        margin-bottom: 25px;
    }

    .input-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 20px;
    }

    .input-group label {
        font-size: 13px;
        font-weight: 600;
        color: #1d1d1f;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
    }

    .input-group input,
    .input-group textarea {
        padding: 14px 18px;
        border-radius: 12px;
        border: 1px solid #d2d2d7;
        font-size: 15px;
        color: #1d1d1f;
        background: #fbfbfd;
        transition: all 0.3s;
    }

    .input-group input:focus,
    .input-group textarea:focus {
        border-color: #1d1d1f;
        background: #fff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.05);
    }

    .input-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .form-row {
        display: flex;
        gap: 20px;
    }

    .form-row .input-group {
        flex: 1;
    }

    .submit-cust-btn {
        width: 100%;
        padding: 16px;
        border-radius: 980px;
        background: #1d1d1f;
        color: #fff;
        font-weight: 600;
        font-size: 16px;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .submit-cust-btn:hover {
        background: #333336;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .alert-success {
        background: #eefaf0;
        color: #34c759;
        padding: 15px 20px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 500;
        line-height: 1.4;
        margin-top: 15px;
        border: 1px solid rgba(52, 199, 89, 0.2);
    }

    .alert-danger {
        background: #ffeeee;
        color: #ff3b30;
        padding: 15px 20px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 500;
        line-height: 1.4;
        margin-top: 15px;
        border: 1px solid rgba(255, 59, 48, 0.2);
    }

    @media(max-width: 992px) {
        .pro-customization-wrapper {
            padding: 0 20px;
        }

        .customization-card {
            flex-direction: column;
            padding: 30px;
            gap: 40px;
        }

        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
</style>

<div class="pro-product-wrapper">
    <div class="pro-gallery">
        <div class="pro-main-img" onclick="openLbx(0)" style="position: relative;">
            <img id="main-view" src="<?= htmlspecialchars($images[0]) ?>" alt="Product View">
            
            <!-- Dynamic Height Dimension Overlay -->
            <div id="dim-overlay-height" style="display: none; position: absolute; right: 25px; top: 40px; bottom: 40px; width: 30px; z-index: 10; transition: all 0.3s ease;">
                <div style="position: absolute; left: 14px; top: 0; bottom: 0; border-left: 2px dashed #d6a86c;"></div>
                <div style="position: absolute; left: 11px; top: 0; width: 8px; height: 8px; border-left: 2px solid #d6a86c; border-top: 2px solid #d6a86c; transform: rotate(45deg);"></div>
                <div style="position: absolute; left: 11px; bottom: 0; width: 8px; height: 8px; border-left: 2px solid #d6a86c; border-bottom: 2px solid #d6a86c; transform: rotate(-45deg);"></div>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #1d1d1f; color: #fff; font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 6px; white-space: nowrap; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    <span id="dim-height-val">0 cm</span>
                </div>
            </div>

            <!-- Dynamic Width Dimension Overlay -->
            <div id="dim-overlay-width" style="display: none; position: absolute; bottom: 25px; left: 40px; right: 40px; height: 30px; z-index: 10; transition: all 0.3s ease;">
                <div style="position: absolute; top: 14px; left: 0; right: 0; border-top: 2px dashed #d6a86c;"></div>
                <div style="position: absolute; left: 0; top: 11px; width: 8px; height: 8px; border-left: 2px solid #d6a86c; border-bottom: 2px solid #d6a86c; transform: rotate(45deg);"></div>
                <div style="position: absolute; right: 0; top: 11px; width: 8px; height: 8px; border-right: 2px solid #d6a86c; border-top: 2px solid #d6a86c; transform: rotate(45deg);"></div>
                <div style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); background: #1d1d1f; color: #fff; font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 6px; white-space: nowrap; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    <span id="dim-width-val">0 cm</span>
                </div>
            </div>
        </div>
        <?php if (count($images) > 1): ?>
            <div class="pro-thumbs-list">
                <?php foreach ($images as $idx => $img): ?>
                    <div class="pro-thumb <?= $idx === 0 ? 'active' : '' ?>" onclick="setMainImg(<?= $idx ?>)">
                        <img src="<?= htmlspecialchars($img) ?>" alt="Thumb <?= $idx ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="pro-details">
        <h1 class="pro-title"><?= htmlspecialchars($product['title']) ?></h1>
        <div style="color: #d6a86c; font-size: 20px; margin-bottom: 25px;">
            <?= str_repeat('★', $product['rating']) . str_repeat('☆', 5 - $product['rating']) ?>
        </div>

        <div class="pro-price">
            <?php if ($product['old_price']): ?>
                <span class="pro-old-price" id="old-price-display">₹<?= number_format($product['old_price'], 2) ?></span>
            <?php endif; ?>
            <span id="price-display">₹<?= number_format($product['price'], 2) ?></span>
        </div>

        <div class="pro-stock-badge">
            <?= htmlspecialchars($product['stock_status']) ?> (<span id="stock-display"><?= $product['stock_quantity'] ?></span> in stock)
        </div>

        <div class="pro-desc">
            <?php 
                $desc = $product['description'] ?? 'Elegantly minimal, durably crafted. Designed to look right at home anywhere.';
                if (strpos($desc, '<p>') !== false || strpos($desc, '<br>') !== false || strpos($desc, '<strong>') !== false || strpos($desc, '<ul>') !== false || strpos($desc, '<li>') !== false) {
                    echo $desc;
                } else {
                    echo nl2br(htmlspecialchars($desc));
                }
            ?>
        </div>

        <?php if (!empty($product_specs)): ?>
            <div class="pro-specs" style="margin-bottom: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                <h3 style="font-size: 16px; font-weight: 600; color: #1d1d1f; margin-bottom: 12px;">Specifications</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tbody>
                        <?php foreach ($product_specs as $spec): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 8px 0; font-weight: 600; font-size: 14px; color: #666; width: 35%;"><?= htmlspecialchars($spec['spec_name']) ?></td>
                                <td style="padding: 8px 0; font-size: 14px; color: #1d1d1f;"><?= htmlspecialchars($spec['spec_value']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if (!empty($unique_colors) || !empty($unique_sizes)): ?>
            <div class="pro-variations" style="margin-bottom: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                
                <?php if (!empty($unique_colors)): ?>
                    <div style="margin-bottom: 20px;">
                        <span style="display: block; font-weight: 600; font-size: 13px; text-transform: uppercase; color: #666; margin-bottom: 8px;">
                            Select Color
                        </span>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <?php foreach ($unique_colors as $c_idx => $color): ?>
                                <label style="cursor: pointer; position: relative;">
                                    <input type="radio" name="selected_color" 
                                           value="<?= htmlspecialchars($color) ?>" 
                                           <?= $c_idx === 0 ? 'checked' : '' ?>
                                           onchange="onColorChange()"
                                           style="position: absolute; opacity: 0; width: 0; height: 0;">
                                    <span class="var-badge" style="display: inline-block; padding: 8px 16px; border-radius: 8px; border: 1px solid #ddd; font-weight: 500; font-size: 13px; background: #fff; color: #1d1d1f; transition: all 0.3s;">
                                        <?= htmlspecialchars($color) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($unique_sizes)): ?>
                    <div style="margin-bottom: 20px;">
                        <span style="display: block; font-weight: 600; font-size: 13px; text-transform: uppercase; color: #666; margin-bottom: 8px;">
                            Select Size
                        </span>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                            <?php foreach ($unique_sizes as $s_idx => $size): ?>
                                <label style="cursor: pointer; position: relative;">
                                    <input type="radio" name="selected_size" 
                                           value="<?= htmlspecialchars($size) ?>" 
                                           <?= $s_idx === 0 ? 'checked' : '' ?>
                                           onchange="onSizeChange()"
                                           style="position: absolute; opacity: 0; width: 0; height: 0;">
                                    <span class="var-badge" style="display: inline-block; padding: 8px 16px; border-radius: 8px; border: 1px solid #ddd; font-weight: 500; font-size: 13px; background: #fff; color: #1d1d1f; transition: all 0.3s;">
                                        <?= htmlspecialchars($size) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
            
            <style>
                input[type="radio"]:checked + .var-badge {
                    border-color: #1d1d1f;
                    background: #1d1d1f;
                    color: #fff;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                }
            </style>
        <?php endif; ?>

        <div class="pro-action-area">
            <div class="pro-qty">
                <button onclick="updateQty(-1)">-</button>
                <input type="text" id="p-qty" value="1" readonly>
                <button onclick="updateQty(1)">+</button>
            </div>
            <button class="pro-add-btn"
                onclick="addToCart(<?= $product['id'] ?>, parseInt(document.getElementById('p-qty').value), event, window.selectedCombinationId)"
                <?= $product['stock_quantity'] <= 0 ? 'disabled' : '' ?>>
                <?= $product['stock_quantity'] <= 0 ? 'Out of Stock' : 'Add to Bag' ?>
            </button>
        </div>
    </div>
</div>

<!-- Customization Form Section -->
<div class="pro-customization-wrapper">
    <div class="customization-card">
        <div class="customization-info">
            <div class="cust-icon">🎨</div>
            <h2>Looking for something unique?</h2>
            <p>We offer premium, bespoke customization options for <strong><?= htmlspecialchars($product['title']) ?></strong>. Whether you need custom dimensions, custom colors, different materials, or unique features, our craftsmen can build it for you.</p>
            <p class="cust-subtext">Fill in your requirements below. Our design consultant will contact you via email or phone within 24 hours with a custom quote.</p>
            <div class="contact-details">
                <div class="contact-item">
                    <span class="c-icon">📞</span>
                    <span><strong>Phone Support:</strong> +1 (800) 123-4567</span>
                </div>
                <div class="contact-item">
                    <span class="c-icon">✉️</span>
                    <span><strong>Design Lab:</strong> designs@archipaws.com</span>
                </div>
            </div>
        </div>
        <div class="customization-form-container">
            <h3>Customization Request</h3>
            <form id="customizationForm" onsubmit="submitCustomization(event)">
                <input type="hidden" name="action" value="submit_customization">
                <input type="hidden" name="product_id" value="<?= $id ?>">
                
                <div class="input-group">
                    <label for="cust_name">Your Name</label>
                    <input type="text" id="cust_name" name="customer_name" value="<?= htmlspecialchars($user_name) ?>" required placeholder="e.g. Jane Doe">
                </div>
                
                <div class="form-row">
                    <div class="input-group">
                        <label for="cust_email">Email Address</label>
                        <input type="email" id="cust_email" name="customer_email" value="<?= htmlspecialchars($user_email) ?>" required placeholder="e.g. jane@example.com">
                    </div>
                    <div class="input-group">
                        <label for="cust_phone">Phone Number</label>
                        <input type="tel" id="cust_phone" name="customer_phone" required placeholder="e.g. +91 98765 43210">
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="cust_details">What would you like us to customize?</label>
                    <textarea id="cust_details" name="customization_details" required placeholder="Specify your desired size, height, width, material type, wood finish, color alterations, or special additions..."></textarea>
                </div>
                
                <button type="submit" class="submit-cust-btn">Send Design Request</button>
            </form>
            <div id="cust-success-msg" class="alert-success" style="display: none;">
                🎉 Thank you! Your request has been sent. Our design team will contact you shortly.
            </div>
            <div id="cust-error-msg" class="alert-danger" style="display: none;"></div>
        </div>
    </div>
</div>

<div class="pro-reviews-wrapper">
    <div class="pro-reviews-header">
        <h2 class="pro-reviews-title">Customer Reviews (<?= count($reviews) ?>)</h2>
        <div class="pro-reviews-filter">
            <select onchange="window.location.href='?id=<?= $id ?>&sort='+this.value">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
            </select>
        </div>
    </div>

    <?php if ($current_user_id): ?>
        <div class="pro-review-form">
            <h3>Write a Review</h3>
            <form id="addReviewForm" onsubmit="submitReview(event)">
                <input type="hidden" name="action" value="add_review">
                <input type="hidden" name="product_id" value="<?= $id ?>">
                <div style="margin: 15px 0;">
                    <label style="margin-right: 15px; font-weight: 600;">Rating:</label>
                    <select name="rating" style="padding: 5px; border-radius: 8px;">
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>
                <textarea name="review_text" placeholder="Share your thoughts about this product..." required></textarea>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Attach a Photo (Optional):</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
                <button type="submit" class="pro-btn-solid">Submit Review</button>
            </form>
        </div>
    <?php else: ?>
        <div style="margin-bottom: 40px; padding: 20px; background: #fff; border-radius: 16px; text-align: center;">
            <p>Please <a href="login.php" style="color: #1d1d1f; font-weight: 600;">log in</a> to write a review.</p>
        </div>
    <?php endif; ?>

    <div class="pro-reviews-list">
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $rev): ?>
                <div class="pro-review-card" id="review-<?= $rev['id'] ?>">
                    <div class="pro-review-head">
                        <div class="pro-review-user"><?= htmlspecialchars($rev['name']) ?></div>
                        <div class="pro-review-date"><?= date('F j, Y', strtotime($rev['created_at'])) ?></div>
                    </div>
                    <div class="pro-review-rating">
                        <?= str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']) ?>
                    </div>
                    <div class="pro-review-text" id="review-text-<?= $rev['id'] ?>">
                        <?= nl2br(htmlspecialchars($rev['review_text'])) ?>
                    </div>
                    <?php if ($rev['photo_path']): ?>
                        <img src="<?= htmlspecialchars($rev['photo_path']) ?>" class="pro-review-photo"
                            onclick="openReviewPhoto(this.src)">
                    <?php endif; ?>

                    <?php if ($rev['admin_reply']): ?>
                        <div class="pro-admin-reply">
                            <div class="pro-admin-reply-label">Archipaws Admin Response:</div>
                            <div><?= nl2br(htmlspecialchars($rev['admin_reply'])) ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($current_user_id == $rev['user_id']): ?>
                        <div class="pro-review-actions">
                            <button class="pro-btn-outline"
                                onclick="editReview(<?= $rev['id'] ?>, <?= $rev['rating'] ?>)">Edit</button>
                            <button class="pro-btn-outline pro-btn-danger" onclick="deleteReview(<?= $rev['id'] ?>)">Delete</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #86868b;">No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>
    </div>
</div>

<!-- Lbx HTML -->
<div id="lbx" class="lbx-overlay">
    <div class="lbx-close" onclick="closeLbx()">&times;</div>
    <div class="lbx-content" onclick="if(event.target===this) closeLbx()">
        <?php if (count($images) > 1): ?>
            <button class="lbx-nav lbx-prev" onclick="navLbx(-1)">❮</button>
        <?php endif; ?>
        <img id="lbx-img" class="lbx-img" src="">
        <?php if (count($images) > 1): ?>
            <button class="lbx-nav lbx-next" onclick="navLbx(1)">❯</button>
        <?php endif; ?>
    </div>
</div>

<script>
    const rawImages = <?= json_encode($images_raw) ?>;
    const combinations = <?= json_encode($combinations) ?>;
    const basePrice = <?= (float)$product['price'] ?>;
    const baseOldPrice = <?= $product['old_price'] ? (float)$product['old_price'] : 'null' ?>;
    const maxQty = <?= $product['stock_quantity'] ?>;
    let activeImages = [];
    let currIdx = 0;

    function onColorChange() {
        filterImagesByColor();
        updateDynamicPriceAndStock();
    }

    function onSizeChange() {
        updateDynamicPriceAndStock();
    }

    function filterImagesByColor() {
        const selectedColorRadio = document.querySelector('input[name="selected_color"]:checked');
        const selectedColor = selectedColorRadio ? selectedColorRadio.value : '';
        
        // General images have color_value = null
        const filtered = rawImages.filter(img => {
            return !img.color_value || img.color_value === selectedColor;
        });
        
        activeImages = filtered.map(img => img.image_path);
        if (activeImages.length === 0) {
            activeImages = ['assets/images/16.jpeg'];
        }
        
        renderGallery();
    }

    function renderGallery() {
        const mainView = document.getElementById('main-view');
        if (mainView && activeImages.length > 0) {
            mainView.src = activeImages[0];
        }
        
        const thumbsContainer = document.querySelector('.pro-thumbs-list');
        if (thumbsContainer) {
            thumbsContainer.innerHTML = '';
            activeImages.forEach((img, idx) => {
                const thumbDiv = document.createElement('div');
                thumbDiv.className = `pro-thumb ${idx === 0 ? 'active' : ''}`;
                thumbDiv.onclick = () => setMainImg(idx);
                thumbDiv.innerHTML = `<img src="${img}" alt="Thumb ${idx}">`;
                thumbsContainer.appendChild(thumbDiv);
            });
        }
        currIdx = 0;
    }

    function updateDynamicPriceAndStock() {
        const selectedColorRadio = document.querySelector('input[name="selected_color"]:checked');
        const selectedSizeRadio = document.querySelector('input[name="selected_size"]:checked');
        
        const color = selectedColorRadio ? selectedColorRadio.value : '';
        const size = selectedSizeRadio ? selectedSizeRadio.value : '';
        
        let match = null;
        if (color && size) {
            match = combinations.find(c => c.color === color && c.size === size);
        } else if (color) {
            match = combinations.find(c => c.color === color);
        } else if (size) {
            match = combinations.find(c => c.size === size);
        }
        
        let priceModifier = 0;
        let selectedStock = maxQty;
        let combinationId = null;
        
        if (match) {
            priceModifier = match.price_modifier;
            selectedStock = match.stock_quantity;
            combinationId = match.id;
        }

        // Update Dimensions Overlays on Main Image
        const overlayHeight = document.getElementById('dim-overlay-height');
        const overlayWidth = document.getElementById('dim-overlay-width');
        
        if (overlayHeight && overlayWidth) {
            if (match && match.height && match.width) {
                overlayHeight.style.display = 'block';
                overlayWidth.style.display = 'block';
                
                document.getElementById('dim-height-val').innerText = match.height + ' cm';
                document.getElementById('dim-width-val').innerText = match.width + ' cm';
            } else {
                overlayHeight.style.display = 'none';
                overlayWidth.style.display = 'none';
            }
        }
        
        const currentPrice = basePrice + priceModifier;
        const priceDisplay = document.getElementById('price-display');
        if (priceDisplay) {
            priceDisplay.innerText = '₹' + currentPrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        
        if (baseOldPrice !== null) {
            const oldPriceDisplay = document.getElementById('old-price-display');
            if (oldPriceDisplay) {
                const currentOldPrice = baseOldPrice + priceModifier;
                oldPriceDisplay.innerText = '₹' + currentOldPrice.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
        
        const stockDisplay = document.getElementById('stock-display');
        if (stockDisplay) {
            stockDisplay.innerText = selectedStock;
        }

        const badge = document.querySelector('.pro-stock-badge');
        if (badge) {
            if (selectedStock <= 0) {
                badge.innerText = 'Sold Out (' + selectedStock + ' in stock)';
                badge.style.color = '#ff3b30';
                badge.style.backgroundColor = '#ffeeee';
            } else if (selectedStock <= 5) {
                badge.innerText = 'Limited Stock (' + selectedStock + ' in stock)';
                badge.style.color = '#ff9500';
                badge.style.backgroundColor = '#fff8e5';
            } else {
                badge.innerText = 'In Stock (' + selectedStock + ' in stock)';
                badge.style.color = '#34c759';
                badge.style.backgroundColor = '#eefaf0';
            }
        }
        
        const qtyInput = document.getElementById('p-qty');
        const addBtn = document.querySelector('.pro-add-btn');
        
        if (selectedStock <= 0) {
            if (addBtn) {
                addBtn.disabled = true;
                addBtn.innerText = 'Out of Stock';
            }
            if (qtyInput) qtyInput.value = '1';
        } else {
            if (addBtn) {
                addBtn.disabled = false;
                addBtn.innerText = 'Add to Bag';
            }
            if (qtyInput && parseInt(qtyInput.value) > selectedStock) {
                qtyInput.value = selectedStock;
            }
        }
        
        window.currentVarStock = selectedStock;
        window.selectedCombinationId = combinationId;
    }

    document.addEventListener('DOMContentLoaded', () => {
        filterImagesByColor();
        updateDynamicPriceAndStock();
    });

    function updateQty(diff) {
        const inp = document.getElementById('p-qty');
        let v = parseInt(inp.value) + diff;
        const currentLimit = (typeof window.currentVarStock !== 'undefined') ? window.currentVarStock : maxQty;
        if (v < 1) v = 1;
        if (v > currentLimit) v = currentLimit;
        inp.value = v;
    }

    function setMainImg(idx) {
        currIdx = idx;
        document.getElementById('main-view').src = activeImages[idx];
        const thumbs = document.querySelectorAll('.pro-thumb');
        thumbs.forEach((t, i) => {
            if (i === idx) t.classList.add('active'); else t.classList.remove('active');
        });
    }

    function openLbx(idx) {
        setMainImg(idx);
        document.getElementById('lbx').style.display = 'block';
        document.getElementById('lbx-img').src = activeImages[currIdx];
        document.querySelectorAll('.lbx-nav').forEach(n => n.style.display = 'flex');
    }
    function closeLbx() {
        document.getElementById('lbx').style.display = 'none';
        document.querySelectorAll('.lbx-nav').forEach(n => n.style.display = 'flex');
    }
    function navLbx(dir) {
        currIdx += dir;
        if (currIdx < 0) currIdx = activeImages.length - 1;
        if (currIdx >= activeImages.length) currIdx = 0;
        document.getElementById('lbx-img').src = activeImages[currIdx];
        setMainImg(currIdx);
    }

    document.addEventListener('keydown', e => {
        if (document.getElementById('lbx').style.display === 'block') {
            if (e.key === 'Escape') closeLbx();
            if (e.key === 'ArrowLeft') navLbx(-1);
            if (e.key === 'ArrowRight') navLbx(1);
        }
    });

    function openReviewPhoto(src) {
        document.getElementById('lbx').style.display = 'block';
        document.getElementById('lbx-img').src = src;
        // Disable nav arrows for single photo
        document.querySelectorAll('.lbx-nav').forEach(n => n.style.display = 'none');
    }

    async function submitReview(e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        try {
            const res = await fetch('ajax/review_action.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error submitting review');
            }
        } catch (err) {
            alert('Network error');
        }
    }

    async function deleteReview(id) {
        if (!confirm('Are you sure you want to delete your review?')) return;
        const fd = new FormData();
        fd.append('action', 'delete_review');
        fd.append('review_id', id);
        try {
            const res = await fetch('ajax/review_action.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                document.getElementById('review-' + id).remove();
            } else {
                alert(data.message || 'Error deleting review');
            }
        } catch (err) {
            alert('Network error');
        }
    }

    async function editReview(id, currentRating) {
        const textEl = document.getElementById('review-text-' + id);
        const currentText = textEl.innerText;
        const newText = prompt("Edit your review:", currentText);
        if (newText === null || newText.trim() === '') return;

        const newRatingStr = prompt("Edit rating (1-5):", currentRating);
        const newRating = parseInt(newRatingStr);
        if (isNaN(newRating) || newRating < 1 || newRating > 5) {
            alert("Invalid rating");
            return;
        }

        const fd = new FormData();
        fd.append('action', 'edit_review');
        fd.append('review_id', id);
        fd.append('rating', newRating);
        fd.append('review_text', newText);

        try {
            const res = await fetch('ajax/review_action.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error editing review');
            }
        } catch (err) {
            alert('Network error');
        }
    }

    async function submitCustomization(e) {
        e.preventDefault();
        const btn = e.target.querySelector('.submit-cust-btn');
        const successMsg = document.getElementById('cust-success-msg');
        const errorMsg = document.getElementById('cust-error-msg');
        
        btn.disabled = true;
        btn.innerText = 'Sending Request...';
        successMsg.style.display = 'none';
        errorMsg.style.display = 'none';
        
        const fd = new FormData(e.target);
        try {
            const res = await fetch('ajax/customization_action.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                successMsg.style.display = 'block';
                errorMsg.style.display = 'none';
                e.target.reset();
            } else {
                errorMsg.innerText = data.message || 'Error sending request';
                errorMsg.style.display = 'block';
                successMsg.style.display = 'none';
            }
        } catch (err) {
            errorMsg.innerText = 'Network error. Please try again.';
            errorMsg.style.display = 'block';
            successMsg.style.display = 'none';
        } finally {
            btn.disabled = false;
            btn.innerText = 'Send Design Request';
        }
    }
</script>

<?php include 'footer.php'; ?>