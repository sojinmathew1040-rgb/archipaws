<?php
require_once '../db.php';
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    header("Location: orders.php");
    exit;
}

$orders = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC")->fetchAll(PDO::FETCH_ASSOC);

function getOrderItems($pdo, $order_id)
{
    $stmt = $pdo->prepare("SELECT oi.*, p.title FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin.css">
    <style>
        .order-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #d6a86c;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .order-header h4 {
            margin: 0;
            color: #1d1d1f;
        }

        .order-meta span {
            display: block;
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .order-items table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 15px;
        }

        .order-items th,
        .order-items td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .order-items th {
            background: #f9f9f9;
        }
    </style>
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
                <li><a href="testimonial_manager.php">Testimonials</a></li>
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="review_manager.php">Reviews</a></li>
                <li><a href="customizations.php">Customizations</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="main-content">
            <div class="header-top">
                <h1>Order Management</h1>
            </div>

            <?php if (empty($orders)): ?>
                <div class="card">
                    <p style="color:#666;">No orders placed yet.</p>
                </div>
            <?php else: ?>
                <?php
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
                $baseUrl = rtrim($protocol . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])), '/\\');

                foreach ($orders as $o):
                    $items = getOrderItems($pdo, $o['id']);
                    $itemsWaText = "";
                    foreach ($items as $item) {
                        $productURL = $baseUrl . '/product.php?id=' . $item['product_id'];
                        $varSuffix = !empty($item['variation_details']) ? " (" . $item['variation_details'] . ")" : "";
                        $itemsWaText .= "• *" . $item['title'] . $varSuffix . "* (Qty: " . $item['quantity'] . ") - ₹" . $item['price'] . "\n  Link: " . $productURL . "\n";
                    }
                    ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <h4>Order #<?= $o['id'] ?></h4>
                                <small style="color:#888;"><?= date('F j, Y, g:i a', strtotime($o['created_at'])) ?></small>
                            </div>
                            <div style="text-align: right;">
                                <strong style="font-size:18px;">₹<?= number_format($o['total_price'], 2) ?></strong>
                                <form method="POST" style="margin-top: 5px;">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <input type="hidden" name="update_status" value="1">
                                    <?php $phoneTrimmed = preg_replace('/[^0-9]/', '', $o['phone']); ?>
                                    <?php
                                    $fullWaMsg = "Hello " . $o['name'] . ", your order #" . $o['id'] . " from ARCHIPAWS Pet Shop has been updated to: STATUS_PLACEHOLDER.\n\n*Order Details:*\n" . $itemsWaText;
                                    ?>
                                    <select name="status"
                                        onchange="updateStatusAndWhatsapp(this, '<?= $phoneTrimmed ?>', '<?= rawurlencode($fullWaMsg) ?>')"
                                        style="padding: 4px; font-size: 13px; font-weight: 600; border-radius: 4px; border: 1px solid #ccc; color: <?= $o['status'] == 'Delivered' ? 'green' : ($o['status'] == 'Pending' ? '#d6a86c' : '#333') ?>;">
                                        <option value="Pending" <?= $o['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Received" <?= $o['status'] == 'Received' ? 'selected' : '' ?>>Received
                                        </option>
                                        <option value="Processing" <?= $o['status'] == 'Processing' ? 'selected' : '' ?>>Processing
                                        </option>
                                        <option value="Shipped" <?= $o['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="Delivered" <?= $o['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered
                                        </option>
                                        <option value="Cancelled" <?= $o['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled
                                        </option>
                                    </select>
                                </form>
                            </div>
                        </div>

                        <div class="order-actions"
                            style="margin-top: -5px; margin-bottom: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                            <?php
                            $wa_msg = rawurlencode(str_replace('STATUS_PLACEHOLDER', $o['status'], $fullWaMsg));
                            ?>
                            <a href="https://wa.me/<?= $phoneTrimmed ?>?text=<?= $wa_msg ?>" target="_blank"
                                style="padding: 8px 16px; background: #25D366; color: white; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: bold;">💬
                                WhatsApp Notify</a>
                            <form action="delete_order.php" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this order?');" style="margin:0;">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <button type="submit"
                                    style="padding: 8px 16px; background: #ff3b30; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold;">🗑️
                                    Delete Order</button>
                            </form>
                        </div>

                        <div style="display:flex; gap: 40px;">
                            <div class="order-meta" style="flex:1;">
                                <strong>Customer Info</strong>
                                <span>Name: <?= htmlspecialchars($o['name']) ?></span>
                                <span>Email: <?= htmlspecialchars($o['email']) ?></span>
                                <span>Phone: <?= htmlspecialchars($o['phone']) ?></span>
                            </div>
                            <div class="order-meta" style="flex:1;">
                                <strong>Shipping Address</strong>
                                <span><?= nl2br(htmlspecialchars($o['shipping_address'])) ?></span>
                            </div>
                        </div>

                        <div class="order-items">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($item['title']) ?>
                                                <?php if (!empty($item['variation_details'])): ?>
                                                    <div style="font-size: 12px; color: #888; margin-top: 3px; font-weight: 500;">
                                                        <?= htmlspecialchars($item['variation_details']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td>₹<?= number_format($item['price'], 2) ?></td>
                                            <td>₹<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>
    <script>
        function updateStatusAndWhatsapp(selectElement, phone, encodedMsgTemplate) {
            let status = selectElement.value;
            let msgTemplate = decodeURIComponent(encodedMsgTemplate);
            let msg = msgTemplate.replace('STATUS_PLACEHOLDER', status);
            let url = "https://wa.me/" + phone + "?text=" + encodeURIComponent(msg);

            // Open WhatsApp in a new tab
            window.open(url, '_blank');

            // Submit the form to save the status
            selectElement.form.submit();
        }
    </script>
</body>

</html>