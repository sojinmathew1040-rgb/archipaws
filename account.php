<?php
require_once 'db.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: logout.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$user_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>

<style>
    /* Apple-Level Pro overrides for account.php */
    body {
        background: #fbfbfd;
        padding-top: 64px;
    }

    .pro-acc-wrap {
        max-width: 1200px;
        margin: 60px auto 100px;
        padding: 0 40px;
    }

    .pro-acc-header {
        margin-bottom: 60px;
        text-align: center;
    }

    .pro-acc-header h1 {
        font-size: 48px;
        font-weight: 700;
        color: #1d1d1f;
        letter-spacing: -0.04em;
        margin-bottom: 10px;
    }

    .pro-acc-header p {
        font-size: 19px;
        color: #86868b;
    }

    .pro-bento-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 40px;
        align-items: start;
    }

    .pro-bento-profile {
        background: #fff;
        border-radius: 32px;
        padding: 40px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        text-align: center;
        border: 1px solid rgba(0, 0, 0, 0.02);
        position: sticky;
        top: 100px;
    }

    .pro-avatar {
        width: 100px;
        height: 100px;
        background: #d6a86c;
        border-radius: 980px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: 600;
        color: #fff;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(214, 168, 108, 0.3);
    }

    .pro-name {
        font-size: 24px;
        font-weight: 600;
        color: #1d1d1f;
        letter-spacing: -0.01em;
        margin-bottom: 5px;
    }

    .pro-email {
        font-size: 15px;
        color: #86868b;
        margin-bottom: 30px;
    }

    .pro-btn-group {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .pro-btn {
        padding: 16px;
        border-radius: 980px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), background 0.3s;
    }

    .pro-btn-dark {
        background: #1d1d1f;
        color: #fff;
    }

    .pro-btn-dark:hover {
        background: #333336;
        transform: scale(0.98);
    }

    .pro-btn-light {
        background: #f5f5f7;
        color: #ff3b30;
    }

    .pro-btn-light:hover {
        background: #ffeeee;
        transform: scale(0.98);
    }

    .pro-bento-orders {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .pro-orders-header {
        font-size: 28px;
        font-weight: 600;
        color: #1d1d1f;
        letter-spacing: -0.02em;
        margin-bottom: 10px;
    }

    .pro-order-card {
        background: #fff;
        border-radius: 24px;
        padding: 30px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.02);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .pro-order-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
    }

    .pro-order-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding-bottom: 20px;
        margin-bottom: 20px;
    }

    .pro-order-id {
        font-size: 19px;
        font-weight: 600;
        color: #1d1d1f;
        margin-bottom: 4px;
    }

    .pro-order-date {
        font-size: 14px;
        color: #86868b;
    }

    .pro-order-status {
        padding: 6px 14px;
        border-radius: 980px;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-delivered {
        background: #eefaf0;
        color: #28a745;
    }

    .status-cancelled {
        background: #ffeeee;
        color: #dc3545;
    }

    .status-shipped {
        background: #ebf5ff;
        color: #007aff;
    }

    .status-pending {
        background: #fff8e5;
        color: #fd7e14;
    }

    .pro-order-detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        font-size: 15px;
    }

    .pro-order-total {
        font-weight: 600;
        color: #1d1d1f;
    }

    .pro-order-addr {
        color: #86868b;
        max-width: 60%;
        text-align: right;
    }

    .pro-order-items {
        display: flex;
        flex-direction: column;
        gap: 15px;
        background: #fbfbfd;
        padding: 20px;
        border-radius: 16px;
    }

    .pro-item-row {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .pro-item-img {
        width: 50px;
        height: 50px;
        background: #fff;
        border-radius: 10px;
        padding: 5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
    }

    .pro-item-img img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .pro-item-desc {
        flex: 1;
        font-size: 14px;
        color: #1d1d1f;
        font-weight: 500;
    }

    .pro-item-qty {
        font-size: 13px;
        color: #86868b;
    }

    @media(max-width: 992px) {
        .pro-acc-wrap {
            padding: 0 20px;
        }

        .pro-bento-grid {
            grid-template-columns: 1fr;
        }

        .pro-bento-profile {
            position: static;
        }
    }
</style>

<div class="pro-acc-wrap">
    <div class="pro-acc-header">
        <h1>Overview.</h1>
        <p>Manage your account and view recent orders.</p>
    </div>

    <div class="pro-bento-grid">
        <div class="pro-bento-profile">
            <div class="pro-avatar"><?= strtoupper(substr(htmlspecialchars($user['name']), 0, 1)) ?></div>
            <div class="pro-name"><?= htmlspecialchars($user['name']) ?></div>
            <div class="pro-email"><?= htmlspecialchars($user['email']) ?></div>

            <div class="pro-btn-group">
                <a href="shop.php" class="pro-btn pro-btn-dark">Continue Shopping</a>
                <a href="logout.php" class="pro-btn pro-btn-light">Sign Out</a>
            </div>
        </div>

        <div class="pro-bento-orders">
            <div class="pro-orders-header">Order History</div>

            <?php if (empty($user_orders)): ?>
                <div
                    style="background: #fff; padding: 60px 40px; border-radius: 32px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.03);">
                    <h3 style="font-size: 24px; font-weight: 600; color: #1d1d1f; margin-bottom: 10px;">No orders yet.</h3>
                    <p style="color: #86868b; margin-bottom: 30px;">Discover our premium selection of pet accessories.</p>
                    <a href="shop.php" class="pro-btn pro-btn-dark" style="display: inline-block;">Shop Now</a>
                </div>
            <?php else: ?>
                <?php foreach ($user_orders as $order): ?>
                    <div class="pro-order-card">
                        <div class="pro-order-top">
                            <div>
                                <div class="pro-order-id">Order #<?= htmlspecialchars($order['id']) ?></div>
                                <div class="pro-order-date"><?= date('F j, Y', strtotime($order['created_at'])) ?></div>
                            </div>
                            <?php
                            $sc = 'status-pending';
                            if ($order['status'] == 'Delivered')
                                $sc = 'status-delivered';
                            else if ($order['status'] == 'Cancelled')
                                $sc = 'status-cancelled';
                            else if ($order['status'] == 'Shipped' || $order['status'] == 'Received')
                                $sc = 'status-shipped';
                            ?>
                            <div class="pro-order-status <?= $sc ?>"><?= htmlspecialchars($order['status']) ?></div>
                        </div>

                        <div class="pro-order-detail-row">
                            <div class="pro-order-total">Total: ₹<?= number_format($order['total_price'], 2) ?></div>
                            <div class="pro-order-addr">
                                <?= htmlspecialchars(substr(str_replace(["\r", "\n"], ", ", $order['shipping_address']), 0, 45)) ?>...
                            </div>
                        </div>

                        <?php
                        $stmtItems = $pdo->prepare("SELECT oi.*, p.title, (SELECT image_path FROM product_images WHERE product_id = p.id ORDER BY sort_order ASC LIMIT 1) as image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                        $stmtItems->execute([$order['id']]);
                        $order_items_list = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="pro-order-items">
                            <?php foreach ($order_items_list as $item): ?>
                                <div class="pro-item-row">
                                    <div class="pro-item-img">
                                        <img src="<?= htmlspecialchars($item['image'] ?? 'assets/images/16.jpeg') ?>">
                                    </div>
                                    <div class="pro-item-desc">
                                        <div><?= htmlspecialchars($item['title']) ?></div>
                                        <?php if (!empty($item['variation_details'])): ?>
                                            <div style="font-size: 11px; color: #86868b; margin-top: 2px; margin-bottom: 2px;"><?= htmlspecialchars($item['variation_details']) ?></div>
                                        <?php endif; ?>
                                        <div class="pro-item-qty">Qty: <?= $item['quantity'] ?> ×
                                            ₹<?= number_format($item['price'], 2) ?></div>
                                    </div>
                                    <div style="font-weight: 600; color: #1d1d1f; font-size: 14px;">
                                        ₹<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>