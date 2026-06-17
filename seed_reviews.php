<?php
require_once 'db.php';

try {
    // 1. Ensure we have a dummy user
    $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
    $user_id = $stmt->fetchColumn();

    if (!$user_id) {
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (name, email, password) VALUES ('Test User', 'testuser@example.com', '$hash')");
        $user_id = $pdo->lastInsertId();
    }

    // 2. Get all products
    $stmt = $pdo->query("SELECT id FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $dummy_reviews = [
        "Absolutely love this product! The quality is amazing.",
        "My pet won't stop playing with this. Highly recommended!",
        "Good value for the price, but shipping was a bit slow.",
        "Elegantly designed and very durable.",
        "Five stars! Will definitely buy from Archipaws again.",
        "A must-have for any pet owner.",
        "Super cute and exactly as described."
    ];

    $count = 0;
    foreach ($products as $product) {
        $product_id = $product['id'];

        // Add 1 or 2 reviews per product
        $num_reviews = rand(1, 2);

        for ($i = 0; $i < $num_reviews; $i++) {
            $review_text = $dummy_reviews[array_rand($dummy_reviews)];
            $rating = rand(4, 5); // Good ratings to make it look nice

            // Add a chance for an admin reply
            $admin_reply = null;
            if (rand(1, 10) > 7) {
                $admin_reply = "Thank you so much for your feedback! We're thrilled you like it.";
            }

            $insertStmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, review_text, admin_reply) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->execute([$product_id, $user_id, $rating, $review_text, $admin_reply]);
            $count++;
        }
    }

    echo "Successfully inserted $count dummy reviews!";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>