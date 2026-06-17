<?php
require_once 'db.php';
header('Content-Type: text/plain');

echo "SEEDING VARIATIONS WITH PRICE DECREMENTS AND INCREMENTS FOR PRODUCT ID 6...\n\n";

try {
    $pdo->beginTransaction();

    // 1. Delete existing variations for product 6
    $pdo->exec("DELETE FROM product_variations WHERE product_id = 6");
    echo "Cleaned existing variations for product ID 6.\n";

    // 2. Insert new combinations with height, width, increments, and decrements
    $stmt = $pdo->prepare("INSERT INTO product_variations (product_id, variation_name, variation_value, price_modifier, stock_quantity, height, width) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Combination 1: Red / Medium (H: 50cm, W: 40cm, Price: Decremented by ₹150.00)
    $stmt->execute([6, 'Combination', 'Red / Medium', -150.00, 5, 50.0, 40.0]);
    $v1_id = $pdo->lastInsertId();
    echo "Added variation: Red / Medium (H: 50cm, W: 40cm, Modifier: -150) - ID: $v1_id\n";

    // Combination 2: Red / Large (H: 80cm, W: 60cm, Price: Incremented by ₹250.00)
    $stmt->execute([6, 'Combination', 'Red / Large', 250.00, 8, 80.0, 60.0]);
    $v2_id = $pdo->lastInsertId();
    echo "Added variation: Red / Large (H: 80cm, W: 60cm, Modifier: +250) - ID: $v2_id\n";

    $pdo->commit();
    echo "\nSeeding complete! Ready for price modifiers and overlays verification.\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error seeding: " . $e->getMessage() . "\n";
}
?>
