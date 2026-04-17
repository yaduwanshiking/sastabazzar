<?php
include 'db.php';

if (!isset($_COOKIE['currentUser'])) {
    header('Location: index.php');
    exit;
}

$user = json_decode($_COOKIE['currentUser'], true);
if (!$user['isAdmin']) {
    header('Location: home.php');
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Order ID', 'Customer', 'Email', 'Phone', 'Total', 'Status', 'Date', 'Address']);

$orders = $conn->query("SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.ordered_date DESC");

while ($row = $orders->fetch_assoc()) {
    fputcsv($output, [
        $row['order_id'],
        $row['delivery_name'] ?? $row['customer_name'],
        $row['delivery_email'] ?? $row['customer_email'],
        $row['delivery_phone'] ?? $row['customer_phone'],
        $row['total'],
        $row['status'],
        date('d-m-Y', strtotime($row['ordered_date'])),
        $row['delivery_address']
    ]);
}

fclose($output);
?>