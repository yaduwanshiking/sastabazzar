<?php
include 'db.php';

if (!isset($_COOKIE['currentUser'])) {
    header('Location: index.php');
    exit;
}
$user = json_decode($_COOKIE['currentUser'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - SastaBazaar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root { --primary: #f97316; --primary-dark: #ea580c; --secondary: #16a34a; --dark: #1e293b; --gray: #64748b; --danger: #ef4444; }
        
        body { background: #f8fafc; }
        
        .navbar {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 0 50px;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }
        
        .nav-logo {
            font-size: 28px;
            font-weight: 900;
            color: var(--primary);
            text-decoration: none;
        }
        
        .orders-page { max-width: 1000px; margin: 0 auto; padding: 50px 20px; }
        .page-title { font-size: 42px; font-weight: 900; margin-bottom: 40px; color: var(--dark); }
        
        .order-card {
            background: white;
            border-radius: 24px;
            padding: 35px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 25px; border-bottom: 3px solid #f1f5f9; flex-wrap: wrap; gap: 15px; }
        .order-info h3 { font-size: 22px; font-weight: 900; color: var(--primary); margin-bottom: 8px; }
        .order-info p { color: var(--gray); font-size: 15px; }
        
        .order-status {
            padding: 12px 25px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
        }
        
        .order-status.confirmed { background: #dcfce7; color: var(--secondary); }
        .order-status.processing { background: #dbeafe; color: #2563eb; }
        .order-status.shipped { background: #fef3c7; color: #d97706; }
        .order-status.delivered { background: #dcfce7; color: var(--secondary); }
        .order-status.cancelled { background: #fee2e2; color: var(--danger); }
        
        .order-item-row { display: flex; gap: 20px; padding: 20px 0; border-bottom: 1px solid #f1f5f9; align-items: center; flex-wrap: wrap; }
        .order-item-row img { width: 80px; height: 80px; border-radius: 16px; object-fit: cover; }
        .order-item-details { flex: 1; }
        .order-item-name { font-size: 18px; font-weight: 800; margin-bottom: 8px; }
        .order-item-price { font-size: 20px; font-weight: 900; color: var(--primary); }
        
        .order-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 25px; border-top: 3px solid #f1f5f9; flex-wrap: wrap; gap: 20px; }
        .order-total { font-size: 28px; font-weight: 900; }
        
        .empty-state { text-align: center; padding: 100px 20px; }
        .empty-icon { font-size: 120px; margin-bottom: 30px; }
        
        .btn-hero {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 18px 45px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 18px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .order-header { flex-direction: column; text-align: center; }
            .order-footer { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="home.php" class="nav-logo">🛒 SastaBazaar</a>
            <a href="home.php" style="color: var(--gray); text-decoration: none;">← Back to Home</a>
        </div>
    </nav>

    <div class="orders-page">
        <h1 class="page-title">📦 My Orders</h1>
        <div id="ordersContainer"></div>
    </div>

    <script>
        let currentUser = null;
        try {
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === 'currentUser') {
                    currentUser = JSON.parse(decodeURIComponent(value));
                    break;
                }
            }
        } catch (e) {
            currentUser = null;
        }
        
        if (!currentUser) {
            window.location.href = 'index.php';
        }

        function loadOrders() {
            fetch('my-orders.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderOrders(data.orders);
                    } else {
                        renderOrders([]);
                    }
                })
                .catch(error => {
                    console.warn('Failed to fetch orders:', error);
                    renderOrders([]);
                });
        }

        function renderOrders(orders) {
            const container = document.getElementById('ordersContainer');
            
            if (orders.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📦</div>
                        <h3>No orders yet</h3>
                        <p>You haven't placed any orders yet.</p>
                        <a href="product.php" class="btn-hero">Start Shopping</a>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = orders.map(order => `
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>${order.order_id}</h3>
                            <p>${new Date(order.ordered_date).toLocaleString()}</p>
                        </div>
                        <span class="order-status ${order.status.toLowerCase()}">${order.status}</span>
                    </div>
                    
                    <div class="order-items">
                        ${order.items.map(item => `
                            <div class="order-item-row">
                                <div class="order-item-details">
                                    <div class="order-item-name">${item.product_name}</div>
                                    <div>Qty: ${item.quantity}</div>
                                </div>
                                <div class="order-item-price">₹${item.price * item.quantity}</div>
                            </div>
                        `).join('')}
                    </div>

                    <div class="order-footer">
                        <div class="order-total">Total: ₹${order.total}</div>
                        <div>
                            ${order.status !== 'Cancelled' && order.status !== 'Delivered' ? 
                                `<button class="btn-hero" style="padding: 12px 25px; font-size: 14px;" onclick="trackOrder('${order.order_id}')">Track Order</button>` : 
                                '<span style="color: #999;">Order Completed</span>'}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function trackOrder(orderId) {
            alert(`📍 Order ${orderId}: Your order is being processed. You will receive updates soon!`);
        }

        loadOrders();
    </script>
</body>
</html>