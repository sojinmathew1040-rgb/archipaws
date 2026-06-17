<?php
// db.php
session_start();

// Google OAuth Configuration
// Replace with your actual Google Client ID from the Google Cloud Console (https://console.cloud.google.com/)
define('GOOGLE_CLIENT_ID', '105645089758-b99k8p7nia6pmr1ts8a73uo78g9av8nr.apps.googleusercontent.com');

$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_pass = '';
$mysql_dbname = 'archipaws_db';

try {
    $pdo = new PDO("mysql:host=$mysql_host;dbname=$mysql_dbname;charset=utf8mb4", $mysql_user, $mysql_pass);
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Ensure all required modern tables exist (self-healing)
    ensure_tables_exist($pdo);
} catch (PDOException $e) {
    // Check if the error is due to an unknown database (1049)
    if ($e->getCode() == 1049 || strpos($e->getMessage(), '1049') !== false || strpos($e->getMessage(), 'Unknown database') !== false) {
        try {
            // 1. Connect to MySQL server without database first
            $pdo = new PDO("mysql:host=$mysql_host;charset=utf8mb4", $mysql_user, $mysql_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 2. Create the database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$mysql_dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

            // 3. Connect to the newly created database
            $pdo = new PDO("mysql:host=$mysql_host;dbname=$mysql_dbname;charset=utf8mb4", $mysql_user, $mysql_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 4. Import the main SQL dump (db/archipaws_db.sql)
            $sqlFile = __DIR__ . '/db/archipaws_db.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);

                // Disable foreign key checks for clean import
                $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
                $pdo->exec($sql);
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
            }

            // 5. Ensure all modern tables exist and are seeded (categories, testimonials, deal of the day, reviews)
            ensure_tables_exist($pdo);
        } catch (PDOException $innerException) {
            die("Database auto-initialization failed: " . $innerException->getMessage());
        }
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Self-healing function to check and create missing tables & seed dummy data
function ensure_tables_exist($pdo)
{
    try {
        // 0. Ensure modern columns exist in products table (non-destructive migrations)
        try {
            $pdo->exec("ALTER TABLE products ADD COLUMN is_trending TINYINT(1) DEFAULT 0");

            // Mark a few products as trending by default to populate the homepage showcase
            $pdo->exec("UPDATE products SET is_trending = 1 WHERE id IN (5, 6, 8)");
        } catch (PDOException $e) {
            // Ignore error if column already exists (SQLSTATE 42S21 / Error 1060)
        }

        try {
            $pdo->exec("ALTER TABLE products ADD COLUMN stock_quantity INT DEFAULT 10");
        } catch (PDOException $e) {
            // Ignore error if column already exists
        }

        try {
            $pdo->exec("ALTER TABLE hero_slides ADD COLUMN phone_number VARCHAR(100) DEFAULT NULL");
        } catch (PDOException $e) {
            // Ignore if already exists
        }

        try {
            $pdo->exec("ALTER TABLE hero_slides ADD COLUMN email_address VARCHAR(100) DEFAULT NULL");
        } catch (PDOException $e) {
            // Ignore if already exists
        }

        try {
            $pdo->exec("ALTER TABLE product_images ADD COLUMN color_value VARCHAR(100) DEFAULT NULL");
        } catch (PDOException $e) {
            // Ignore if already exists
        }

        try {
            $pdo->exec("ALTER TABLE order_items ADD COLUMN variation_details VARCHAR(255) DEFAULT NULL");
        } catch (PDOException $e) {
            // Ignore if already exists
        }

        try {
            $pdo->exec("ALTER TABLE product_variations ADD COLUMN height DECIMAL(10,2) DEFAULT NULL");
        } catch (PDOException $e) {
            // Ignore if already exists
        }

        try {
            $pdo->exec("ALTER TABLE product_variations ADD COLUMN width DECIMAL(10,2) DEFAULT NULL");
        } catch (PDOException $e) {
            // Ignore if already exists
        }


        // 1. Create categories table
        $hasCategories = $pdo->query("SHOW TABLES LIKE 'categories'")->fetch() !== false;
        if (!$hasCategories) {
            $pdo->exec("CREATE TABLE categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                image_path VARCHAR(255) DEFAULT 'assets/images/placeholder.jpg',
                sort_order INT DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $pdo->exec("INSERT INTO categories (name, image_path, sort_order) VALUES 
                ('dog', 'uploads/1775542067_0_pic4.webp', 1),
                ('cat', 'assets/images/placeholder.jpg', 2),
                ('bird', 'assets/images/placeholder.jpg', 3)
            ");
        }

        // 2. Create testimonials table
        $hasTestimonials = $pdo->query("SHOW TABLES LIKE 'testimonials'")->fetch() !== false;
        if (!$hasTestimonials) {
            $pdo->exec("CREATE TABLE testimonials (
                id INT AUTO_INCREMENT PRIMARY KEY,
                customer_name VARCHAR(255) NOT NULL,
                quote TEXT NOT NULL,
                rating INT DEFAULT 5,
                image_path VARCHAR(255) DEFAULT 'assets/images/user-placeholder.jpg',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $pdo->exec("INSERT INTO testimonials (customer_name, quote, rating) VALUES 
                ('Sojin Mathew', 'Archipaws pet shop has the best premium kennels ever. Very happy with the quality!', 5),
                ('Dijo', 'Extremely durable teak wood dog house. Five stars!', 5)
            ");
        }

        // 3. Create deal_of_the_day table
        $hasDeals = $pdo->query("SHOW TABLES LIKE 'deal_of_the_day'")->fetch() !== false;
        if (!$hasDeals) {
            $pdo->exec("CREATE TABLE deal_of_the_day (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                end_time DATETIME NOT NULL,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            $prodStmt = $pdo->query("SELECT id FROM products LIMIT 1");
            $prod_id = $prodStmt->fetchColumn();
            if ($prod_id) {
                $endTime = date('Y-m-d H:i:s', strtotime('+7 days'));
                $pdo->exec("INSERT INTO deal_of_the_day (product_id, end_time) VALUES ($prod_id, '$endTime')");
            }
        }

        // 4. Create product_reviews table
        $hasReviews = $pdo->query("SHOW TABLES LIKE 'product_reviews'")->fetch() !== false;
        if (!$hasReviews) {
            $pdo->exec("CREATE TABLE product_reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                user_id INT NOT NULL,
                rating INT NOT NULL DEFAULT 5,
                review_text TEXT NOT NULL,
                photo_path VARCHAR(255) DEFAULT NULL,
                admin_reply TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

            // Seed dummy reviews
            $userStmt = $pdo->query("SELECT id FROM users LIMIT 1");
            $user_id = $userStmt->fetchColumn();
            if (!$user_id) {
                $hash = password_hash('password123', PASSWORD_DEFAULT);
                $pdo->exec("INSERT INTO users (name, email, password) VALUES ('Test User', 'testuser@example.com', '$hash')");
                $user_id = $pdo->lastInsertId();
            }

            $prodStmt = $pdo->query("SELECT id FROM products");
            $products = $prodStmt->fetchAll(PDO::FETCH_ASSOC);

            $dummy_reviews = [
                "Absolutely love this product! The quality is amazing.",
                "My pet won't stop playing with this. Highly recommended!",
                "Good value for the price, but shipping was a bit slow.",
                "Elegantly designed and very durable.",
                "Five stars! Will definitely buy from Archipaws again.",
                "A must-have for any pet owner.",
                "Super cute and exactly as described."
            ];

            foreach ($products as $product) {
                $product_id = $product['id'];
                $num_reviews = rand(1, 2);
                for ($i = 0; $i < $num_reviews; $i++) {
                    $review_text = $dummy_reviews[array_rand($dummy_reviews)];
                    $rating = rand(4, 5);
                    $admin_reply = (rand(1, 10) > 7) ? "Thank you so much for your feedback! We're thrilled you like it." : null;

                    $insertStmt = $pdo->prepare("INSERT INTO product_reviews (product_id, user_id, rating, review_text, admin_reply) VALUES (?, ?, ?, ?, ?)");
                    $insertStmt->execute([$product_id, $user_id, $rating, $review_text, $admin_reply]);
                }
            }
        }

        // 5. Create product_specifications table
        $hasSpecs = $pdo->query("SHOW TABLES LIKE 'product_specifications'")->fetch() !== false;
        if (!$hasSpecs) {
            $pdo->exec("CREATE TABLE product_specifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                spec_name VARCHAR(255) NOT NULL,
                spec_value VARCHAR(255) NOT NULL,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }

        // 6. Create product_variations table
        $hasVariations = $pdo->query("SHOW TABLES LIKE 'product_variations'")->fetch() !== false;
        if (!$hasVariations) {
            $pdo->exec("CREATE TABLE product_variations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                variation_name VARCHAR(255) NOT NULL,
                variation_value VARCHAR(255) NOT NULL,
                price_modifier DECIMAL(10,2) DEFAULT 0.00,
                stock_quantity INT DEFAULT 10,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }

        // 7. Create product_customizations table
        $hasCustomizations = $pdo->query("SHOW TABLES LIKE 'product_customizations'")->fetch() !== false;
        if (!$hasCustomizations) {
            $pdo->exec("CREATE TABLE product_customizations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                customer_name VARCHAR(255) NOT NULL,
                customer_email VARCHAR(255) NOT NULL,
                customer_phone VARCHAR(50) NOT NULL,
                customization_details TEXT NOT NULL,
                status VARCHAR(50) DEFAULT 'Pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
        }
    } catch (PDOException $ignore) {
        // Suppress errors to let database connection succeed in case of minor metadata locks
    }
}

// Helper to check logged in admin
function isAdmin()
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}