<?php
require_once 'db.php';
include 'header.php';

$cart_items = $_SESSION['cart'] ?? [];
$total_price = 0;

$unique_product_ids = [];
$unique_variation_ids = [];

foreach ($cart_items as $key => $qty) {
    $parts = explode('_', $key);
    $prod_id = (int)$parts[0];
    $var_id = isset($parts[1]) ? (int)$parts[1] : 0;
    
    if ($prod_id > 0) {
        $unique_product_ids[$prod_id] = $prod_id;
    }
    if ($var_id > 0) {
        $unique_variation_ids[$var_id] = $var_id;
    }
}

$products_by_id = [];
if (count($unique_product_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($unique_product_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT p.* 
        FROM products p 
        WHERE p.id IN ($placeholders)
    ");
    $stmt->execute(array_values($unique_product_ids));
    $fetched = $stmt->fetchAll();
    foreach ($fetched as $p) {
        $products_by_id[$p['id']] = $p;
    }
}

$variations_by_id = [];
if (count($unique_variation_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($unique_variation_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT * 
        FROM product_variations 
        WHERE id IN ($placeholders)
    ");
    $stmt->execute(array_values($unique_variation_ids));
    $fetched_vars = $stmt->fetchAll();
    foreach ($fetched_vars as $v) {
        $variations_by_id[$v['id']] = $v;
    }
}

$images_by_product_and_color = [];
if (count($unique_product_ids) > 0) {
    $placeholders = implode(',', array_fill(0, count($unique_product_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT product_id, image_path, color_value 
        FROM product_images 
        WHERE product_id IN ($placeholders) 
        ORDER BY sort_order ASC
    ");
    $stmt->execute(array_values($unique_product_ids));
    $fetched_imgs = $stmt->fetchAll();
    foreach ($fetched_imgs as $img) {
        $prod_id = $img['product_id'];
        $color = $img['color_value'];
        $path = $img['image_path'];
        
        if (!isset($images_by_product_and_color[$prod_id]['default'])) {
            $images_by_product_and_color[$prod_id]['default'] = $path;
        }
        
        if ($color) {
            $color_clean = trim(strtolower($color));
            if (!isset($images_by_product_and_color[$prod_id]['colors'][$color_clean])) {
                $images_by_product_and_color[$prod_id]['colors'][$color_clean] = $path;
            }
        }
    }
}

$cart_display_items = [];
foreach ($cart_items as $key => $qty) {
    $parts = explode('_', $key);
    $prod_id = (int)$parts[0];
    $var_id = isset($parts[1]) ? (int)$parts[1] : 0;
    
    if (!isset($products_by_id[$prod_id])) {
        continue;
    }
    
    $product = $products_by_id[$prod_id];
    $title = $product['title'];
    $price = (float)$product['price'];
    $var_info = '';
    $image_path = $images_by_product_and_color[$prod_id]['default'] ?? 'assets/images/16.jpeg';
    
    if ($var_id > 0 && isset($variations_by_id[$var_id])) {
        $variation = $variations_by_id[$var_id];
        $var_val = $variation['variation_value'];
        $parts_val = explode('/', $var_val);
        $color = trim($parts_val[0]);
        $size = isset($parts_val[1]) ? trim($parts_val[1]) : '';
        
        $attributes = [];
        if ($color !== '') {
            $attributes[] = "Color: " . $color;
            $color_clean = trim(strtolower($color));
            if (isset($images_by_product_and_color[$prod_id]['colors'][$color_clean])) {
                $image_path = $images_by_product_and_color[$prod_id]['colors'][$color_clean];
            }
        }
        if ($size !== '') {
            $attributes[] = "Size: " . $size;
        }
        
        $var_info = implode(', ', $attributes);
        $price += (float)$variation['price_modifier'];
    }
    
    $subtotal = $price * $qty;
    $total_price += $subtotal;
    
    $cart_display_items[] = [
        'key' => $key,
        'product_id' => $prod_id,
        'variation_id' => $var_id,
        'title' => $title,
        'var_info' => $var_info,
        'price' => $price,
        'qty' => $qty,
        'image_path' => $image_path,
        'subtotal' => $subtotal
    ];
}
?>

<style>
    body {
        background: #fbfbfd;
        padding-top: 64px;
    }

    .pro-cart-container {
        max-width: 1200px;
        margin: 60px auto 100px;
        padding: 0 40px;
        display: flex;
        gap: 60px;
        flex-wrap: wrap;
    }

    .pro-cart-items {
        flex: 2;
        min-width: 300px;
    }

    .pro-cart-header {
        font-size: 40px;
        font-weight: 700;
        color: #1d1d1f;
        letter-spacing: -0.03em;
        margin-bottom: 30px;
    }

    .pro-cart-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .pro-cart-table th {
        text-align: left;
        padding: 0 0 20px 0;
        color: #86868b;
        font-weight: 500;
        font-size: 14px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .pro-cart-table td {
        padding: 30px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        vertical-align: middle;
    }

    .pro-cart-product {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .pro-cart-img {
        width: 90px;
        height: 90px;
        border-radius: 16px;
        background: #fff;
        padding: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pro-cart-img img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .pro-cart-title {
        font-size: 18px;
        font-weight: 600;
        color: #1d1d1f;
        letter-spacing: -0.01em;
        margin-bottom: 5px;
    }

    .pro-cart-remove {
        color: #007aff;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: 0.3s;
    }

    .pro-cart-remove:hover {
        opacity: 0.7;
    }

    .pro-cart-price {
        font-size: 17px;
        font-weight: 500;
        color: #1d1d1f;
    }

    .pro-qty-control {
        display: inline-flex;
        align-items: center;
        background: #fff;
        border-radius: 980px;
        padding: 4px 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .pro-qty-control button {
        background: none;
        border: none;
        font-size: 18px;
        color: #1d1d1f;
        cursor: pointer;
        padding: 0 8px;
        transition: 0.3s;
    }

    .pro-qty-control button:hover {
        color: #d6a86c;
    }

    .pro-qty-control input {
        width: 30px;
        text-align: center;
        border: none;
        font-size: 15px;
        font-weight: 600;
        color: #1d1d1f;
        background: transparent;
        pointer-events: none;
    }

    .pro-cart-summary {
        flex: 1;
        min-width: 300px;
    }

    .pro-summary-card {
        background: #fff;
        border-radius: 32px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        position: sticky;
        top: 100px;
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .pro-summary-title {
        font-size: 24px;
        font-weight: 700;
        color: #1d1d1f;
        letter-spacing: -0.02em;
        margin-bottom: 25px;
    }

    .pro-summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 15px;
        color: #86868b;
    }

    .pro-summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        font-size: 20px;
        font-weight: 600;
        color: #1d1d1f;
    }

    .pro-checkout-btn {
        width: 100%;
        padding: 16px;
        background: #1d1d1f;
        color: #fff;
        border: none;
        border-radius: 980px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 30px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .pro-checkout-btn:hover {
        background: #333336;
        transform: scale(0.98);
    }

    @media(max-width: 768px) {
        .pro-cart-container {
            padding: 0 20px;
            flex-direction: column;
        }

        .pro-cart-table thead {
            display: none;
        }

        .pro-cart-table tr {
            display: flex;
            flex-direction: column;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 30px 0;
        }

        .pro-cart-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border: none;
        }

        .pro-cart-table td::before {
            content: attr(data-label);
            font-weight: 500;
            color: #86868b;
        }

        .pro-cart-product {
            width: 100%;
        }
    }
</style>

<div class="pro-cart-container">
    <?php if (empty($cart_display_items)): ?>
        <div style="flex:1; text-align: center; padding: 80px 0;">
            <h2 style="font-size: 32px; font-weight: 700; color: #1d1d1f; margin-bottom: 20px;">Your bag is empty.</h2>
            <p style="color: #86868b; margin-bottom: 40px;">Free delivery and free returns on all eligible orders.</p>
            <button class="pro-checkout-btn" style="width: auto; padding: 16px 40px;"
                onclick="window.location='shop.php'">Continue Shopping</button>
        </div>
    <?php else: ?>
        <div class="pro-cart-items">
            <h1 class="pro-cart-header">Review your bag.</h1>
            <p style="color: #86868b; margin-bottom: 40px;">Free delivery and free returns.</p>

            <table class="pro-cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_display_items as $item): ?>
                        <tr>
                            <td data-label="">
                                <div class="pro-cart-product">
                                    <div class="pro-cart-img">
                                        <img src="<?= htmlspecialchars($item['image_path']) ?>"
                                            alt="<?= htmlspecialchars($item['title']) ?>">
                                    </div>
                                    <div>
                                        <div class="pro-cart-title"><?= htmlspecialchars($item['title']) ?></div>
                                        <?php if ($item['var_info'] !== ''): ?>
                                            <div style="font-size: 13px; color: #86868b; margin-top: 4px; margin-bottom: 4px;"><?= htmlspecialchars($item['var_info']) ?></div>
                                        <?php endif; ?>
                                        <a class="pro-cart-remove" onclick="updateBagQty('<?= $item['key'] ?>', 0)">Remove</a>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Price" class="pro-cart-price">₹<?= number_format($item['price'], 2) ?></td>
                            <td data-label="Quantity">
                                <div class="pro-qty-control">
                                    <button onclick="updateBagQty('<?= $item['key'] ?>', <?= $item['qty'] - 1 ?>)">-</button>
                                    <input type="text" value="<?= $item['qty'] ?>" readonly>
                                    <button onclick="updateBagQty('<?= $item['key'] ?>', <?= $item['qty'] + 1 ?>)">+</button>
                                </div>
                            </td>
                            <td data-label="Subtotal" class="pro-cart-price" style="font-weight: 600;">
                                ₹<?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pro-cart-summary">
            <div class="pro-summary-card">
                <div class="pro-summary-title">Summary</div>
                <div class="pro-summary-row">
                    <span>Subtotal</span>
                    <span>₹<?= number_format($total_price, 2) ?></span>
                </div>
                <div class="pro-summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="pro-summary-total">
                    <span>Total</span>
                    <span>₹<?= number_format($total_price, 2) ?></span>
                </div>
                <button class="pro-checkout-btn" onclick="window.location='checkout.php'">Check Out</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function updateBagQty(cartKey, newQty) {
        if (newQty < 0) newQty = 0;
        fetch('ajax/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_key: cartKey, action: 'update', qty: newQty })
        }).then(r => r.json()).then(data => {
            if (data.success) window.location.reload();
        });
    }
</script>

<?php include 'footer.php'; ?>