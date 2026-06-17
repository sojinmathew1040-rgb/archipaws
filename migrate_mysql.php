<?php
// migrate_mysql.php
// This script migrates data from the existing SQLite database to a new MySQL database.

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sqlite_db_file = __DIR__ . '/data/database.sqlite';

$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_pass = '';
$mysql_dbname = 'archipaws_db';

try {
    // 1. Connect to SQLite
    if (!file_exists($sqlite_db_file)) {
        die("SQLite database file not found at: $sqlite_db_file");
    }
    $sqliteDb = new PDO("sqlite:" . $sqlite_db_file);
    $sqliteDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to SQLite...<br>";

    // 2. Connect to MySQL Server (Without DB first)
    $mysqlServer = new PDO("mysql:host=$mysql_host;charset=utf8mb4", $mysql_user, $mysql_pass);
    $mysqlServer->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3. Create MySQL Database if not exists
    $mysqlServer->exec("CREATE DATABASE IF NOT EXISTS `$mysql_dbname`");
    echo "MySQL Database '$mysql_dbname' checked/created...<br>";

    // 4. Connect to the specific MySQL Database
    $mysqlDb = new PDO("mysql:host=$mysql_host;dbname=$mysql_dbname;charset=utf8mb4", $mysql_user, $mysql_pass);
    $mysqlDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable foreign key checks for migration to avoid dependency order issues during creation
    $mysqlDb->exec("SET FOREIGN_KEY_CHECKS=0;");

    // 5. Define MySQL Schemas
    $schemas = [
        "admins" => "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL
        )",
        "hero_slides" => "CREATE TABLE IF NOT EXISTS hero_slides (
            id INT AUTO_INCREMENT PRIMARY KEY,
            offer_text VARCHAR(255),
            title_line1 VARCHAR(255),
            title_line2 VARCHAR(255),
            button_text VARCHAR(255),
            button_link VARCHAR(255),
            image_path VARCHAR(255),
            sort_order INT DEFAULT 0
        )",
        "products" => "CREATE TABLE IF NOT EXISTS products (
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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "product_images" => "CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT,
            image_path VARCHAR(255) NOT NULL,
            sort_order INT DEFAULT 0,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )",
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "orders" => "CREATE TABLE IF NOT EXISTS orders (
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
        )",
        "order_items" => "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )"
    ];

    // 6. Migrate Table by Table
    foreach ($schemas as $tableName => $createSql) {
        // Create table in MySQL
        $mysqlDb->exec($createSql);
        echo "Table '$tableName' created...<br>";

        // Try reading from SQLite
        try {
            $stmt = $sqliteDb->query("SELECT * FROM $tableName");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                // Prepare Insert Statement for MySQL
                $columns = array_keys($rows[0]);
                $colStr = implode(", ", array_map(function ($c) {
                    return "`$c`";
                }, $columns));
                $placeholders = implode(", ", array_map(function ($c) {
                    return ":$c";
                }, $columns));

                $insertSql = "INSERT IGNORE INTO `$tableName` ($colStr) VALUES ($placeholders)";
                $insertStmt = $mysqlDb->prepare($insertSql);

                $count = 0;
                foreach ($rows as $row) {
                    // Check if stock_quantity is missing from product rows (SQLite schema might not have had it yet)
                    if ($tableName == 'products' && !isset($row['stock_quantity'])) {
                        $row['stock_quantity'] = 10;
                    }
                    $insertStmt->execute($row);
                    $count++;
                }
                echo "Migrated $count rows into '$tableName'.<br>";
            } else {
                echo "No data in SQLite '$tableName', skipped insertion.<br>";
            }

        } catch (Exception $e) {
            // SQLite table might not exist, skip silently or show minor message
            echo "<span style='color:orange;'>Table '$tableName' does not exist in SQLite or error reading: " . $e->getMessage() . "</span><br>";
        }
    }

    $mysqlDb->exec("SET FOREIGN_KEY_CHECKS=1;");
    echo "<br><b style='color:green'>Migration Completed Successfully!</b><br>";
    echo "You can now update your db.php to use MySQL.";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>