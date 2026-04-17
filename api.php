<?php
include 'db.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'products') {
    $products = [];
    $result = $conn->query('SELECT id, name, category, price, old_price, image, badge, discount, stock FROM products ORDER BY id DESC');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $row['id'] = (int)$row['id'];
            $row['price'] = (float)$row['price'];
            $row['old_price'] = (float)$row['old_price'];
            $row['discount'] = (int)$row['discount'];
            $row['stock'] = (int)$row['stock'];
            $products[] = $row;
        }
    }
    echo json_encode(['products' => $products]);
    exit;
}

if ($action === 'product') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        echo json_encode(new stdClass());
        exit;
    }

    $stmt = $conn->prepare('SELECT id, name, category, price, old_price, image, badge, discount, stock FROM products WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if ($product) {
        $product['id'] = (int)$product['id'];
        $product['price'] = (float)$product['price'];
        $product['old_price'] = (float)$product['old_price'];
        $product['discount'] = (int)$product['discount'];
        $product['stock'] = (int)$product['stock'];
        echo json_encode($product);
    } else {
        echo json_encode(new stdClass());
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
