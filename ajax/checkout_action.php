<?php
require_once '../db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$cart_items = $_SESSION['cart'] ?? [];
if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$address = trim($data['address'] ?? '');

if (empty($name) || empty($email) || empty($phone) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'All shipping fields are required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Parse Cart Items and fetch products/variations details
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
        $stmt = $pdo->prepare("SELECT id, price, stock_quantity, title FROM products WHERE id IN ($placeholders)");
        $stmt->execute(array_values($unique_product_ids));
        $fetched = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fetched as $p) {
            $products_by_id[$p['id']] = $p;
        }
    }
    
    $variations_by_id = [];
    if (count($unique_variation_ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($unique_variation_ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM product_variations WHERE id IN ($placeholders)");
        $stmt->execute(array_values($unique_variation_ids));
        $fetched_vars = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fetched_vars as $v) {
            $variations_by_id[$v['id']] = $v;
        }
    }
    
    $total_price = 0;
    $order_items_to_save = [];
    
    foreach ($cart_items as $key => $qty) {
        $parts = explode('_', $key);
        $prod_id = (int)$parts[0];
        $var_id = isset($parts[1]) ? (int)$parts[1] : 0;
        
        if (!isset($products_by_id[$prod_id])) {
            throw new Exception("Product ID $prod_id not found.");
        }
        
        $product = $products_by_id[$prod_id];
        $item_price = (float)$product['price'];
        $var_info = null;
        
        if ($var_id > 0) {
            if (!isset($variations_by_id[$var_id])) {
                throw new Exception("Variation ID $var_id not found for product " . $product['title']);
            }
            $variation = $variations_by_id[$var_id];
            
            // Validate variation stock
            if ($variation['stock_quantity'] < $qty) {
                throw new Exception("Insufficient stock for variation: " . $product['title'] . " (" . $variation['variation_value'] . "). Available: " . $variation['stock_quantity']);
            }
            
            $item_price += (float)$variation['price_modifier'];
            
            // Build variation details string
            $var_val = $variation['variation_value'];
            $parts_val = explode('/', $var_val);
            $color = trim($parts_val[0]);
            $size = isset($parts_val[1]) ? trim($parts_val[1]) : '';
            
            $attributes = [];
            if ($color !== '') {
                $attributes[] = "Color: " . $color;
            }
            if ($size !== '') {
                $attributes[] = "Size: " . $size;
            }
            $var_info = implode(', ', $attributes);
        } else {
            // Validate product stock
            if ($product['stock_quantity'] < $qty) {
                throw new Exception("Insufficient stock for product: " . $product['title'] . ". Available: " . $product['stock_quantity']);
            }
        }
        
        $total_price += $item_price * $qty;
        
        $order_items_to_save[] = [
            'product_id' => $prod_id,
            'variation_id' => $var_id,
            'quantity' => $qty,
            'price' => $item_price,
            'variation_details' => $var_info
        ];
    }

    // Apply Offer if active
    if (isset($_SESSION['offer_applied']) && $_SESSION['offer_applied'] === true) {
        $total_price -= ($total_price * 0.20);
        unset($_SESSION['offer_applied']); // One-time use per session
    }

    // 2. Create Order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, name, email, phone, shipping_address, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $name, $email, $phone, $address, $total_price]);
    $order_id = $pdo->lastInsertId();

    // 3. Create Order Items and update stock
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, variation_details) VALUES (?, ?, ?, ?, ?)");
    $updateVarStockStmt = $pdo->prepare("UPDATE product_variations SET stock_quantity = stock_quantity - ? WHERE id = ?");
    $updateProdStockStmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
    
    foreach ($order_items_to_save as $item) {
        $stmtItem->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price'],
            $item['variation_details']
        ]);
        
        if ($item['variation_id'] > 0) {
            $updateVarStockStmt->execute([$item['quantity'], $item['variation_id']]);
            $updateProdStockStmt->execute([$item['quantity'], $item['product_id']]);
        } else {
            $updateProdStockStmt->execute([$item['quantity'], $item['product_id']]);
        }
    }

    $pdo->commit();

    // 4. Clear Cart
    unset($_SESSION['cart']);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error placing order: ' . $e->getMessage()]);
}
?>