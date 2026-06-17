<?php
require 'db.php';
try {
    $pdo->exec("ALTER TABLE products ADD COLUMN stock_quantity INTEGER DEFAULT 10");
    echo "Column added successfully.";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'duplicate column name') !== false) {
        echo "Column already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>