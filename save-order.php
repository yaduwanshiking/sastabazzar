<?php
include 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    die(json_encode(['success' => false, 'message' => 'Invalid JSON data']));
}

if (!isset($data['userId']) || !isset($data['items']) || !isset($data['total'])) {
    die(json_encode(['success' => false, 'message' => 'Missing required fields']));
}

try {
    $user_id = intval($data['userId']);
    $order_id = $data['orderId'] ?? ('ORD' . time());
    $total = floatval($data['total']);
    $status = $data['status'] ?? 'Confirmed';
    
    $delivery_name = $data['address']['name'] ?? '';
    $delivery_email = $data['address']['email'] ?? '';
    $delivery_phone = $data['address']['phone'] ?? '';
    $delivery_address = $data['address']['address'] ?? '';
    $delivery_city = $data['address']['city'] ?? '';
    $delivery_pincode = $data['address']['pincode'] ?? '';

    $stmt = $conn->prepare("INSERT INTO orders (order_id, user_id, total, status, delivery_name, delivery_email, delivery_phone, delivery_address, delivery_city, delivery_pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sidsssssss", $order_id, $user_id, $total, $status, $delivery_name, $delivery_email, $delivery_phone, $delivery_address, $delivery_city, $delivery_pincode);
    
    if (!$stmt->execute()) {
        throw new Exception("Order insertion failed: " . $stmt->error);
    }
    
    $order_db_id = $conn->insert_id;
    $stmt->close();

    $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($data['items'] as $item) {
        $product_id = $item['id'] ?? null;
        $product_name = $item['name'] ?? '';
        $quantity = intval($item['quantity'] ?? 1);
        $price = floatval($item['price'] ?? 0);
        
        $stmt2->bind_param("iisii", $order_db_id, $product_id, $product_name, $quantity, $price);
        $stmt2->execute();
    }
    $stmt2->close();

    echo json_encode(['success' => true, 'message' => 'Order saved successfully', 'orderId' => $order_id]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>