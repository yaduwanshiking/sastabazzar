<?php
include 'db.php';
header('Content-Type: application/json');

if (!isset($_COOKIE['currentUser'])) {
    die(json_encode(['error' => 'Unauthorized']));
}
$user = json_decode($_COOKIE['currentUser'], true);
if (!$user['isAdmin']) {
    die(json_encode(['error' => 'Unauthorized']));
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'stats':
        $revenue = $conn->query("SELECT SUM(total) as total FROM orders WHERE status != 'Cancelled'")->fetch_assoc()['total'] ?? 0;
        $orders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'];
        $users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
        $products = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
        echo json_encode(['revenue' => $revenue, 'orders' => $orders, 'users' => $users, 'items' => $products]);
        break;
        
    case 'orders':
        $result = $conn->query("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.ordered_date DESC");
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $items = $conn->query("SELECT * FROM order_items WHERE order_id = " . $row['id']);
            $row['items'] = $items->fetch_all(MYSQLI_ASSOC);
            $orders[] = $row;
        }
        echo json_encode(['orders' => $orders]);
        break;
        
    case 'users':
        $result = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count FROM users u ORDER BY u.created_at DESC");
        echo json_encode(['users' => $result->fetch_all(MYSQLI_ASSOC)]);
        break;
        
    case 'products':
        $result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
        echo json_encode(['products' => $result->fetch_all(MYSQLI_ASSOC)]);
        break;
        
    case 'add_product':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, old_price, image, badge, discount, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddsssi", $data['name'], $data['category'], $data['price'], $data['oldPrice'], $data['image'], $data['badge'], $data['discount'], $data['stock']);
        echo json_encode(['success' => $stmt->execute()]);
        $stmt->close();
        break;
        
    case 'delete_product':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $data['product_id']);
        echo json_encode(['success' => $stmt->execute()]);
        $stmt->close();
        break;
        
    case 'update_order_status':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("ss", $data['status'], $data['order_id']);
        echo json_encode(['success' => $stmt->execute()]);
        $stmt->close();
        break;
        
    case 'delete_order':
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->bind_param("s", $data['order_id']);
        echo json_encode(['success' => $stmt->execute()]);
        $stmt->close();
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>