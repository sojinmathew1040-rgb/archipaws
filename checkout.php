<?php
require_once 'db.php';

$cart_items = $_SESSION['cart'] ?? [];
if (empty($cart_items)) {
    header("Location: shop.php");
    exit;
}

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

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
$total_price = 0;

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

$discount = 0;
if (isset($_SESSION['offer_applied']) && $_SESSION['offer_applied'] === true) {
    $discount = $total_price * 0.20;
    $total_price -= $discount;
}

include 'header.php';
?>

<style>
    /* Apple-Level Pro style overrides for checkout.php */
    body {
        background: #fbfbfd;
        padding-top: 64px;
    }

    .pro-chk-container {
        max-width: 1200px;
        margin: 40px auto 80px;
        padding: 0 40px;
        display: flex;
        gap: 60px;
        align-items: flex-start;
    }

    .pro-chk-main {
        flex: 2;
        min-width: 300px;
    }

    .pro-chk-header {
        font-size: 40px;
        font-weight: 700;
        color: #1d1d1f;
        letter-spacing: -0.03em;
        margin-bottom: 10px;
    }

    .pro-chk-subtitle {
        font-size: 15px;
        color: #86868b;
        margin-bottom: 40px;
    }

    .pro-chk-group {
        background: #fff;
        border-radius: 24px;
        padding: 40px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .pro-chk-group-title {
        font-size: 21px;
        font-weight: 600;
        color: #1d1d1f;
        letter-spacing: -0.01em;
        margin-bottom: 25px;
    }

    .pro-input-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .pro-input-col {
        flex: 1;
    }

    .pro-input-col label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #1d1d1f;
        margin-bottom: 8px;
    }

    .pro-input-col input,
    .pro-input-col textarea {
        width: 100%;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        padding: 16px;
        font-size: 16px;
        color: #1d1d1f;
        box-sizing: border-box;
        background: #fcfcfd;
        transition: all 0.3s;
    }

    .pro-input-col input:focus,
    .pro-input-col textarea:focus {
        outline: none;
        border-color: #007aff;
        box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
        background: #fff;
    }


    .pro-chk-summary {
        flex: 1;
        min-width: 300px;
        position: sticky;
        top: 100px;
    }

    .pro-sum-card {
        background: #fff;
        border-radius: 24px;
        padding: 30px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.02);
    }

    .pro-item-row {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .pro-item-img {
        width: 60px;
        height: 60px;
        background: #f5f5f7;
        border-radius: 12px;
        padding: 8px;
    }

    .pro-item-img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .pro-item-info {
        flex: 1;
    }

    .pro-item-info h4 {
        font-size: 15px;
        font-weight: 600;
        color: #1d1d1f;
        margin-bottom: 4px;
    }

    .pro-item-info p {
        font-size: 13px;
        color: #86868b;
    }

    .pro-item-price {
        font-size: 15px;
        font-weight: 600;
        color: #1d1d1f;
    }

    .pro-totals-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 15px;
        color: #86868b;
    }

    .pro-totals-final {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        font-size: 20px;
        font-weight: 600;
        color: #1d1d1f;
    }

    .pro-pay-btn {
        width: 100%;
        padding: 18px;
        background: #1d1d1f;
        color: #fff;
        border: none;
        border-radius: 980px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 25px;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), background 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .pro-pay-btn:hover {
        background: #333336;
        transform: scale(0.98);
    }

    .success-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #ffffff;
        z-index: 9999;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .success-overlay h2 {
        font-size: 40px;
        font-weight: 700;
        color: #1d1d1f;
        margin: 20px 0 10px;
    }

    .success-overlay p {
        font-size: 18px;
        color: #86868b;
        margin-bottom: 40px;
    }

    .success-overlay button {
        padding: 16px 32px;
        background: #1d1d1f;
        color: #fff;
        border: none;
        border-radius: 980px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.3s;
    }

    .success-overlay button:hover {
        background: #333336;
    }

    @media(max-width: 992px) {
        .pro-chk-container {
            flex-direction: column;
            padding: 0 20px;
        }

        .pro-chk-summary {
            position: static;
            width: 100%;
        }

        .pro-input-row {
            flex-direction: column;
            gap: 0;
            margin-bottom: 0;
        }

        .pro-input-col {
            margin-bottom: 20px;
        }
    }
</style>

<div class="pro-chk-container">
    <div class="pro-chk-main">
        <div class="pro-chk-header">Checkout.</div>
        <div class="pro-chk-subtitle">Fast, secure, and encrypted.</div>

        <form id="checkoutForm">
            <div class="pro-chk-group">
                <div class="pro-chk-group-title">Shipping Information</div>

                <div class="pro-input-row">
                    <div class="pro-input-col">
                        <label>Full Name</label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                    </div>
                    <div class="pro-input-col">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="+1 (555) 000-0000" required
                            value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="pro-input-row">
                    <div class="pro-input-col">
                        <label>Email Address</label>
                        <input type="email" name="email" required value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                            readonly
                            style="background: transparent; border-color:transparent; padding:0; color:#86868b;">
                    </div>
                </div>

                <div class="pro-input-row">
                    <div class="pro-input-col">
                        <label>Delivery Address</label>
                        <textarea name="address" rows="3"
                            required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="pro-chk-group">
                <div class="pro-chk-group-title">Secure Payment</div>
                <div style="display: flex; gap: 8px; margin-bottom: 20px;">
                    <span
                        style="background: #f5f5f7; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; color: #1d1d1f;">VISA</span>
                    <span
                        style="background: #f5f5f7; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; color: #1d1d1f;">MasterCard</span>
                    <span
                        style="background: #f5f5f7; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; color: #1d1d1f;">Amex</span>
                </div>

                <div class="pro-input-row">
                    <div class="pro-input-col">
                        <label>Card Number</label>
                        <input type="text" placeholder="0000 0000 0000 0000" maxlength="19" required>
                    </div>
                </div>

                <div class="pro-input-row">
                    <div class="pro-input-col">
                        <label>Expiry (MM/YY)</label>
                        <input type="text" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div class="pro-input-col">
                        <label>Security Code</label>
                        <input type="text" placeholder="123" maxlength="3" required>
                    </div>
                </div>

                <button type="submit" class="pro-pay-btn" id="payBtn">
                    <span id="btnText">Place Order — ₹<?= number_format($total_price, 2) ?></span>
                    <svg id="btnSpinner" style="display:none; animation: spin 1s linear infinite;" width="20"
                        height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                        <path d="M12 2v4" stroke-opacity="1" />
                    </svg>
                </button>
            </div>
        </form>
    </div>

    <div class="pro-chk-summary">
        <div class="pro-sum-card">
            <h3 style="font-size: 21px; font-weight: 600; color: #1d1d1f; margin-bottom: 25px;">Order Summary</h3>

            <div style="margin-bottom: 25px; max-height: 350px; overflow-y: auto;">
                <?php foreach ($cart_display_items as $item): ?>
                    <div class="pro-item-row">
                        <div class="pro-item-img"><img
                                src="<?= htmlspecialchars($item['image_path']) ?>"></div>
                        <div class="pro-item-info">
                            <h4><?= htmlspecialchars($item['title']) ?></h4>
                            <?php if ($item['var_info'] !== ''): ?>
                                <p style="font-size: 11px; margin-bottom: 2px;"><?= htmlspecialchars($item['var_info']) ?></p>
                            <?php endif; ?>
                            <p>Qty: <?= $item['qty'] ?></p>
                        </div>
                        <div class="pro-item-price">₹<?= number_format($item['subtotal'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div>
                <div class="pro-totals-row">
                    <span>Subtotal</span>
                    <span>₹<?= number_format($total_price + $discount, 2) ?></span>
                </div>
                <?php if ($discount > 0): ?>
                    <div class="pro-totals-row" style="color: #34c759; font-weight: 500;">
                        <span>Discount applied</span>
                        <span>-₹<?= number_format($discount, 2) ?></span>
                    </div>
                <?php endif; ?>
                <div class="pro-totals-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="pro-totals-final">
                    <span>Total</span>
                    <span>₹<?= number_format($total_price, 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="success-overlay" id="successOverlay">
    <svg width="100" height="100" viewBox="0 0 100 100" fill="none">
        <circle cx="50" cy="50" r="48" fill="#34c759" />
        <path d="M30 50L45 65L75 35" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <h2>Order Confirmed</h2>
    <p>We'll email you an order receipt with details.</p>
    <button onclick="window.location='account.php'">View My Orders</button>
</div>

<script>
    document.getElementById("checkoutForm").addEventListener("submit", async function (e) {
        e.preventDefault();
        const btn = document.getElementById("payBtn");
        const btnText = document.getElementById("btnText");
        const btnSpinner = document.getElementById("btnSpinner");

        btnText.textContent = "Processing Securely...";
        btnSpinner.style.display = "block";
        btn.disabled = true;

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        try {
            const res = await fetch('ajax/checkout_action.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
            const result = await res.json();

            if (result.success) {
                document.getElementById("successOverlay").style.display = "flex";
            } else {
                alert(result.message || "Error.");
                btnText.textContent = "Place Order — ₹<?= number_format($total_price, 2) ?>";
                btnSpinner.style.display = "none";
                btn.disabled = false;
            }
        } catch (e) {
            alert("Network Error.");
            btnText.textContent = "Place Order — ₹<?= number_format($total_price, 2) ?>";
            btnSpinner.style.display = "none";
            btn.disabled = false;
        }
    });

    document.querySelector('input[placeholder="0000 0000 0000 0000"]').addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
    });
</script>

<?php include 'footer.php'; ?>