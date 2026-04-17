<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "bazar_db";

// Create database if it doesn't exist
$rootConn = new mysqli($host, $user, $password);
if ($rootConn->connect_error) {
    die("Connection failed: " . $rootConn->connect_error);
}
$rootConn->query("CREATE DATABASE IF NOT EXISTS `$database`");
$rootConn->close();

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Create users table
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    isAdmin TINYINT(1) NOT NULL DEFAULT 0,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create products table
$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    old_price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(500) NOT NULL,
    badge VARCHAR(100),
    discount INT DEFAULT 0,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create orders table
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Confirmed',
    delivery_name VARCHAR(100),
    delivery_email VARCHAR(100),
    delivery_phone VARCHAR(20),
    delivery_address TEXT,
    delivery_city VARCHAR(100),
    delivery_pincode VARCHAR(10),
    ordered_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

// Create order_items table
$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(255),
    quantity INT DEFAULT 1,
    price DECIMAL(10, 2),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
)");

// Create default admin if not exists
$adminEmail = 'admin@sastabazaar.com';
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$adminCheck = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$adminCheck->bind_param('s', $adminEmail);
$adminCheck->execute();
$adminCheck->store_result();
if ($adminCheck->num_rows === 0) {
    $adminInsert = $conn->prepare('INSERT INTO users (name, email, phone, password, isAdmin, address) VALUES (?, ?, ?, ?, ?, ?)');
    $adminName = 'Admin';
    $adminPhone = '9999999999';
    $adminIsAdmin = 1;
    $adminAddress = 'Admin Office';
    $adminInsert->bind_param('ssssss', $adminName, $adminEmail, $adminPhone, $adminPassword, $adminIsAdmin, $adminAddress);
    $adminInsert->execute();
    $adminInsert->close();
}
$adminCheck->close();

// Insert default products if empty
$productCheck = $conn->query("SELECT COUNT(*) as count FROM products");
$productCount = $productCheck->fetch_assoc()['count'];
if ($productCount == 0) {
    $defaultProducts = [
        ['Wireless Earbuds Pro', 'Electronics', 999, 2999, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=400&h=300&fit=crop', '70% OFF', 70, 50],
        ['Smart Watch Ultra', 'Electronics', 1499, 4999, 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=400&h=300&fit=crop', '70% OFF', 70, 30],
        ['Bluetooth Speaker', 'Electronics', 799, 2499, 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=400&h=300&fit=crop', '68% OFF', 68, 45],
        ['Premium Phone Case', 'Accessories', 599, 1499, 'https://images.unsplash.com/photo-1603313011101-320f26a4f6f6?w=400&h=300&fit=crop', '60% OFF', 60, 100],
        ['Gaming Mouse', 'Electronics', 1299, 3499, 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=400&h=300&fit=crop', '63% OFF', 63, 40],
        ['Laptop Backpack', 'Accessories', 899, 2499, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=300&fit=crop', '64% OFF', 64, 60]
    ];
    
    $stmt = $conn->prepare("INSERT INTO products (name, category, price, old_price, image, badge, discount, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($defaultProducts as $p) {
        $stmt->bind_param("ssddsssi", $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7]);
        $stmt->execute();
    }
    $stmt->close();
}
?>