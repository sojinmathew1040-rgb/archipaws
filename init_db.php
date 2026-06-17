<?php
// init_db.php
// This script initializes the MySQL database.

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_pass = '';
$mysql_dbname = 'archipaws_db';

try {
    // 1. Connect to MySQL Server (Without DB first)
    $mysqlServer = new PDO("mysql:host=$mysql_host;charset=utf8mb4", $mysql_user, $mysql_pass);
    $mysqlServer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create MySQL Database if not exists
    $mysqlServer->exec("CREATE DATABASE IF NOT EXISTS `$mysql_dbname`");

    // 3. Connect to the specific MySQL Database
    $pdo = new PDO("mysql:host=$mysql_host;dbname=$mysql_dbname;charset=utf8mb4", $mysql_user, $mysql_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable foreign key checks for migration to avoid dependency order issues during creation
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

    // 4. Create Tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS hero_slides (
        id INT AUTO_INCREMENT PRIMARY KEY,
        offer_text VARCHAR(255),
        title_line1 VARCHAR(255),
        title_line2 VARCHAR(255),
        button_text VARCHAR(255),
        button_link VARCHAR(255),
        image_path VARCHAR(255),
        sort_order INT DEFAULT 0
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        old_price DECIMAL(10,2) DEFAULT NULL,
        category VARCHAR(100),
        badge VARCHAR(50),
        rating INT DEFAULT 5,
        stock_status VARCHAR(50) DEFAULT 'In Stock',
        stock_quantity INT DEFAULT 10,
        is_trending TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        image_path VARCHAR(255) DEFAULT 'assets/images/placeholder.jpg',
        sort_order INT DEFAULT 0
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        quote TEXT NOT NULL,
        rating INT DEFAULT 5,
        image_path VARCHAR(255) DEFAULT 'assets/images/user-placeholder.jpg',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS deal_of_the_day (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        end_time DATETIME NOT NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT,
        image_path VARCHAR(255) NOT NULL,
        sort_order INT DEFAULT 0,
        color_value VARCHAR(100) DEFAULT NULL,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        shipping_address TEXT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        variation_details VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    // 5. Insert dummy data if empty

    // Admin
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admins (username, password) VALUES ('admin', '$hash')");
    }

    // Hero Slides
    $stmt = $pdo->query("SELECT COUNT(*) FROM hero_slides");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO hero_slides (offer_text, title_line1, title_line2, button_text, button_link, image_path, sort_order) VALUES ('SAVE 10 - 20 % OFF', 'Best Destination', 'Your Pets', 'SHOP NOW →', 'shop.php', 'assets/images/12.jpeg', 1)");
        $pdo->exec("INSERT INTO hero_slides (offer_text, title_line1, title_line2, button_text, button_link, image_path, sort_order) VALUES ('NEW COLLECTION', 'Comfort Living', 'Your Dogs', 'SHOP NOW →', 'shop.php', 'assets/images/13.jpeg', 2)");
        $pdo->exec("INSERT INTO hero_slides (offer_text, title_line1, title_line2, button_text, button_link, image_path, sort_order) VALUES ('LIMITED DEAL', 'Premium Homes', 'Your Pets', 'SHOP NOW →', 'shop.php', 'assets/images/14.jpeg', 3)");
    }

    // Product
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO products (title, description, price, old_price, category, badge) VALUES ('Grey Hoodle', 'Give your pet the ultimate comfort.', 18.00, 25.00, 'dog', 'new')");
    }

    // Product Images
    $stmt = $pdo->query("SELECT COUNT(*) FROM product_images");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (1, 'assets/images/16.jpeg', 1)");
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
    echo "Database initialized successfully in MySQL!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>