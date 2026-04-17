<?php
include 'db.php';

// Check if admin is logged in
if (!isset($_COOKIE['currentUser'])) {
    header('Location: index.php');
    exit;
}

$user = json_decode($_COOKIE['currentUser'], true);
if (!$user['isAdmin']) {
    header('Location: home.php');
    exit;
}

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_product') {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $old_price = $_POST['old_price'];
        $image = $_POST['image'];
        $badge = $_POST['badge'];
        $discount = $_POST['discount'];
        $stock = $_POST['stock'];
        
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, old_price, image, badge, discount, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddsssi", $name, $category, $price, $old_price, $image, $badge, $discount, $stock);
        $stmt->execute();
        $stmt->close();
        header('Location: admin.php?success=Product Added');
        exit;
    }
    
    // Handle Delete Product
    if ($_POST['action'] === 'delete_product') {
        $id = $_POST['product_id'];
        $conn->query("DELETE FROM products WHERE id = $id");
        header('Location: admin.php?success=Product Deleted');
        exit;
    }
    
    // Handle Update Order Status
    if ($_POST['action'] === 'update_status') {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        $conn->query("UPDATE orders SET status = '$status' WHERE order_id = '$order_id'");
        header('Location: admin.php?success=Status Updated');
        exit;
    }
    
    // Handle Delete Order
    if ($_POST['action'] === 'delete_order') {
        $order_id = $_POST['order_id'];
        $conn->query("DELETE FROM orders WHERE order_id = '$order_id'");
        header('Location: admin.php?success=Order Deleted');
        exit;
    }
}

// Get Stats
$revenueResult = $conn->query("SELECT SUM(total) as total FROM orders WHERE status != 'Cancelled'");
$totalRevenue = $revenueResult->fetch_assoc()['total'] ?? 0;

$ordersResult = $conn->query("SELECT COUNT(*) as count FROM orders");
$totalOrders = $ordersResult->fetch_assoc()['count'] ?? 0;

$usersResult = $conn->query("SELECT COUNT(*) as count FROM users");
$totalUsers = $usersResult->fetch_assoc()['count'] ?? 0;

$productsResult = $conn->query("SELECT COUNT(*) as count FROM products");
$totalProducts = $productsResult->fetch_assoc()['count'] ?? 0;

// Get Recent Orders
$recentOrders = $conn->query("SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.ordered_date DESC LIMIT 5");

// Get All Products
$allProducts = $conn->query("SELECT * FROM products ORDER BY created_at DESC");

// Get All Orders
$allOrders = $conn->query("SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.ordered_date DESC");

// Get All Users
$allUsers = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count FROM users u ORDER BY u.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - SastaBazaar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root { --primary: #f97316; --admin-primary: #6366f1; --admin-dark: #4f46e5; --dark: #1e293b; --gray: #64748b; --danger: #ef4444; --success: #16a34a; }
        
        body { background: #f1f5f9; }
        
        .admin-layout { display: flex; min-height: 100vh; }
        
        .admin-sidebar {
            width: 280px;
            background: var(--dark);
            color: white;
            position: fixed;
            height: 100%;
            overflow-y: auto;
            padding: 30px 0;
        }
        
        .admin-logo { padding: 0 30px 30px; border-bottom: 1px solid #334155; margin-bottom: 30px; cursor: pointer; }
        .admin-logo h2 { font-size: 28px; font-weight: 900; }
        .admin-logo span { color: var(--admin-primary); }
        
        .admin-nav { padding: 0 20px; }
        
        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px 20px;
            margin-bottom: 8px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            color: #94a3b8;
        }
        
        .admin-nav-item:hover, .admin-nav-item.active { background: var(--admin-primary); color: white; }
        
        .admin-main { flex: 1; margin-left: 280px; padding: 40px; }
        
        .admin-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 40px; 
            flex-wrap: wrap; 
            gap: 20px; 
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .admin-header h1 { font-size: 32px; font-weight: 900; color: var(--dark); }
        
        .admin-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 40px; }
        
        .admin-stat-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .admin-stat-icon { width: 70px; height: 70px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 32px; }
        .admin-stat-icon.blue { background: #dbeafe; }
        .admin-stat-icon.green { background: #dcfce7; }
        .admin-stat-icon.orange { background: #ffedd5; }
        .admin-stat-icon.red { background: #fee2e2; }
        
        .admin-stat-info h3 { font-size: 32px; font-weight: 900; color: var(--dark); }
        .admin-stat-info p { color: var(--gray); font-weight: 600; }
        
        .admin-card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px; }
        .admin-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .admin-card-title { font-size: 24px; font-weight: 900; color: var(--dark); }
        
        .admin-btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            background: var(--admin-primary);
            color: white;
            transition: all 0.3s;
        }
        
        .admin-btn:hover { background: var(--admin-dark); transform: translateY(-2px); }
        .admin-btn.secondary { background: #f1f5f9; color: var(--dark); }
        .admin-btn.danger { background: var(--danger); }
        
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        .admin-table th { color: var(--gray); font-weight: 700; font-size: 14px; }
        .admin-table td { color: var(--dark); }
        
        .admin-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }
        .admin-status.Pending { background: #fef3c7; color: #d97706; }
        .admin-status.Confirmed { background: #dbeafe; color: #2563eb; }
        .admin-status.Processing { background: #e0e7ff; color: #4f46e5; }
        .admin-status.Shipped { background: #ffedd5; color: #d97706; }
        .admin-status.Delivered { background: #dcfce7; color: var(--success); }
        .admin-status.Cancelled { background: #fee2e2; color: var(--danger); }
        
        .admin-actions { display: flex; gap: 10px; }
        .admin-action-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }
        .admin-action-btn.delete { background: #fee2e2; color: var(--danger); }
        .admin-action-btn.delete:hover { background: var(--danger); color: white; }
        
        .admin-section { display: none; }
        .admin-section.active { display: block; }
        
        .admin-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }
        .admin-modal.show { display: flex; }
        
        .admin-modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: var(--dark); }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--admin-primary); }
        
        .success-msg {
            background: #dcfce7;
            color: #166534;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-img { width: 50px; height: 50px; border-radius: 10px; object-fit: cover; }
        
        @media (max-width: 768px) {
            .admin-sidebar { width: 100%; position: relative; height: auto; }
            .admin-main { margin-left: 0; padding: 20px; }
            .admin-table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>

    <div class="admin-layout">
        <div class="admin-sidebar">
            <div class="admin-logo" onclick="window.location.href='admin.php'">
                <h2>⚙️ <span>Admin</span>Panel</h2>
            </div>
            <div class="admin-nav">
                <div class="admin-nav-item active" onclick="showSection('dashboard')">📊 Dashboard</div>
                <div class="admin-nav-item" onclick="showSection('products')">📦 Products</div>
                <div class="admin-nav-item" onclick="showSection('orders')">🛒 Orders</div>
                <div class="admin-nav-item" onclick="showSection('users')">👥 Users</div>
                <div class="admin-nav-item" onclick="window.location.href='home.php'" style="margin-top: 20px;">🏠 View Site</div>
                <div class="admin-nav-item" onclick="logout()">🚪 Logout</div>
            </div>
        </div>

        <div class="admin-main">
            <!-- Success Message -->
            <?php if (isset($_GET['success'])): ?>
            <div class="success-msg">
                ✅ <?= htmlspecialchars($_GET['success']) ?>
                <span onclick="this.parentElement.style.display='none'" style="cursor:pointer;">&times;</span>
            </div>
            <?php endif; ?>
            
            <!-- Dashboard Section -->
            <div id="dashboard" class="admin-section active">
                <div class="admin-header">
                    <div>
                        <h1>Dashboard Overview</h1>
                        <p style="color: var(--gray); margin-top: 5px;">Welcome back, <?= htmlspecialchars($user['name']) ?>!</p>
                    </div>
                    <button class="admin-btn secondary" onclick="location.reload()">🔄 Refresh</button>
                </div>

                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon blue">💰</div>
                        <div class="admin-stat-info">
                            <h3>₹<?= number_format($totalRevenue, 0) ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon green">📦</div>
                        <div class="admin-stat-info">
                            <h3><?= $totalOrders ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon orange">👥</div>
                        <div class="admin-stat-info">
                            <h3><?= $totalUsers ?></h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon red">🏷️</div>
                        <div class="admin-stat-info">
                            <h3><?= $totalProducts ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">📈 Recent Orders</h3>
                    </div>
                    <table class="admin-table">
                        <thead>
                            <tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                                <?php while($order = $recentOrders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= substr($order['order_id'], -6) ?></td>
                                    <td><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></td>
                                    <td><?= date('d M Y', strtotime($order['ordered_date'])) ?></td>
                                    <td style="color: var(--primary); font-weight: 800;">₹<?= number_format($order['total'], 2) ?></td>
                                    <td><span class="admin-status <?= $order['status'] ?>"><?= $order['status'] ?></span></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center;">No orders yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Products Section -->
            <div id="products" class="admin-section">
                <div class="admin-header">
                    <h1>Manage Products</h1>
                    <button class="admin-btn" onclick="openProductModal()">+ Add Product</button>
                </div>
                <div class="admin-card">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($allProducts && $allProducts->num_rows > 0): ?>
                                <?php while($product = $allProducts->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <img src="<?= htmlspecialchars($product['image']) ?>" class="product-img" onerror="this.src='https://via.placeholder.com/50'">
                                            <div>
                                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                                                <br><small style="color: var(--gray);">ID: #<?= $product['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span style="background:#f1f5f9;padding:6px 12px;border-radius:20px;"><?= $product['category'] ?></span></td>
                                    <td style="font-weight:800;color:var(--primary);">₹<?= $product['price'] ?></td>
                                    <td><?= $product['stock'] ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?')">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                            <button type="submit" class="admin-action-btn delete">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center;">No products found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Orders Section -->
            <div id="orders" class="admin-section">
                <div class="admin-header">
                    <h1>Manage Orders</h1>
                    <button class="admin-btn" onclick="exportOrders()">📥 Export CSV</button>
                </div>
                <div class="admin-card">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Order ID</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($allOrders && $allOrders->num_rows > 0): ?>
                                <?php while($order = $allOrders->fetch_assoc()): 
                                    $itemsCount = $conn->query("SELECT COUNT(*) as c FROM order_items WHERE order_id = " . $order['id'])->fetch_assoc()['c'];
                                ?>
                                <tr>
                                    <td>#<?= substr($order['order_id'], -6) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($order['delivery_name'] ?? $order['customer_name'] ?? 'Guest') ?></strong>
                                        <br><small><?= htmlspecialchars($order['delivery_email'] ?? '') ?></small>
                                    </td>
                                    <td><?= $itemsCount ?> items</td>
                                    <td style="font-weight:800;color:var(--primary);">₹<?= number_format($order['total'], 2) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <select name="status" onchange="this.form.submit()" style="padding:8px;border-radius:8px;">
                                                <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                                <option value="Confirmed" <?= $order['status'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                                <option value="Processing" <?= $order['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                                <option value="Shipped" <?= $order['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                                <option value="Delivered" <?= $order['status'] == 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                                <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this order?')">
                                            <input type="hidden" name="action" value="delete_order">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <button type="submit" class="admin-action-btn delete">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align: center;">No orders found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users" class="admin-section">
                <div class="admin-header">
                    <h1>Manage Users</h1>
                </div>
                <div class="admin-card">
                    <table class="admin-table">
                        <thead>
                            <tr><th>User</th><th>Email</th><th>Phone</th><th>Orders</th><th>Joined</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($allUsers && $allUsers->num_rows > 0): ?>
                                <?php while($userRow = $allUsers->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#ea580c);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;">
                                                <?= strtoupper(substr($userRow['name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($userRow['name']) ?></strong>
                                                <br><small>ID: #<?= $userRow['id'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($userRow['email']) ?></td>
                                    <td><?= htmlspecialchars($userRow['phone'] ?? 'N/A') ?></td>
                                    <td><?= $userRow['order_count'] ?? 0 ?></td>
                                    <td><?= date('d M Y', strtotime($userRow['created_at'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center;">No users found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="admin-modal" id="productModal">
        <div class="admin-modal-content">
            <h2 style="margin-bottom: 20px;">Add New Product</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_product">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="Electronics">Electronics</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Home">Home</option>
                        <option value="Fashion">Fashion</option>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Price (₹)</label>
                        <input type="number" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Old Price (₹)</label>
                        <input type="number" name="old_price" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" value="50">
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="url" name="image" required placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label>Badge Text</label>
                    <input type="text" name="badge" placeholder="e.g., 70% OFF">
                </div>
                <div class="form-group">
                    <label>Discount %</label>
                    <input type="number" name="discount" value="0">
                </div>
                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="submit" class="admin-btn">💾 Save Product</button>
                    <button type="button" class="admin-btn secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showSection(section) {
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.admin-nav-item').forEach(n => n.classList.remove('active'));
            event.target.closest('.admin-nav-item').classList.add('active');
            document.getElementById(section).classList.add('active');
        }

        function openProductModal() {
            document.getElementById('productModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('productModal').classList.remove('show');
        }

        function exportOrders() {
            window.location.href = 'export-orders.php';
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                document.cookie = "currentUser=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                window.location.href = 'index.php';
            }
        }

        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>