<?php
include 'db.php';
header('Content-Type: application/json');

if (!isset($_COOKIE['currentUser'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in', 'orders' => []]);
    exit;
}

$user = json_decode($_COOKIE['currentUser'], true);
$user_id = $user['id'];

$result = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY ordered_date DESC");

$orders = [];
while ($row = $result->fetch_assoc()) {
    $itemsResult = $conn->query("SELECT * FROM order_items WHERE order_id = " . $row['id']);
    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
    }
    
    $orders[] = [
        'order_id' => $row['order_id'],
        'total' => $row['total'],
        'status' => $row['status'],
        'ordered_date' => $row['ordered_date'],
        'items' => $items
    ];
}

echo json_encode(['success' => true, 'orders' => $orders]);
?>