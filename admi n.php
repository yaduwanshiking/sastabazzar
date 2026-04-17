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
        
        .admin-table { width: 100%; border-collapse: collapse; overflow-x: auto; display: block; }
        .admin-table th, .admin-table td { padding: 15px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        .admin-table th { color: var(--gray); font-weight: 700; font-size: 14px; }
        .admin-table td { color: var(--dark); }
        
        .admin-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .admin-status.pending { background: #fef3c7; color: #d97706; }
        .admin-status.confirmed { background: #dbeafe; color: #2563eb; }
        .admin-status.processing { background: #e0e7ff; color: #4f46e5; }
        .admin-status.shipped { background: #ffedd5; color: #d97706; }
        .admin-status.delivered { background: #dcfce7; color: var(--success); }
        .admin-status.cancelled { background: #fee2e2; color: var(--danger); }
        
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
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--admin-primary); }
        
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--dark);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            font-weight: 700;
            z-index: 99999;
            animation: slideUp 0.3s;
        }
        .toast.success { background: var(--success); }
        .toast.error { background: var(--danger); }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
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
            <div id="dashboard" class="admin-section active">
                <div class="admin-header">
                    <div>
                        <h1>Dashboard Overview</h1>
                        <p style="color: var(--gray); margin-top: 5px;">Welcome back, <span id="adminName">Admin</span>!</p>
                    </div>
                    <button class="admin-btn secondary" onclick="refreshDashboard()">🔄 Refresh</button>
                </div>

                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon blue">💰</div>
                        <div class="admin-stat-info">
                            <h3 id="totalRevenue">₹0</h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon green">📦</div>
                        <div class="admin-stat-info">
                            <h3 id="totalOrders">0</h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon orange">👥</div>
                        <div class="admin-stat-info">
                            <h3 id="totalUsers">0</h3>
                            <p>Total Users</p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-icon red">🏷️</div>
                        <div class="admin-stat-info">
                            <h3 id="totalProducts">0</h3>
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
                        <tbody id="recentOrders"></tbody>
                    </table>
                </div>
            </div>

            <div id="products" class="admin-section">
                <div class="admin-header">
                    <h1>Manage Products</h1>
                    <button class="admin-btn" onclick="openProductModal()">+ Add Product</button>
                </div>
                <div class="admin-card">
                    <table class="admin-table">
                        <thead><tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
                        <tbody id="productsTable"></tbody>
                    </table>
                </div>
            </div>

            <div id="orders" class="admin-section">
                <div class="admin-header">
                    <h1>Manage Orders</h1>
                    <div style="display: flex; gap: 10px;">
                        <button class="admin-btn secondary" onclick="refreshOrders()">🔄 Refresh</button>
                        <button class="admin-btn" onclick="exportOrders()">📥 Export CSV</button>
                    </div>
                </div>
                <div class="admin-card">
                    <table class="admin-table">
                        <thead><tr><th>Order ID</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody id="ordersTable"></tbody>
                    </table>
                </div>
            </div>

            <div id="users" class="admin-section">
                <div class="admin-header">
                    <h1>Manage Users</h1>
                </div>
                <div class="admin-card">
                    <table class="admin-table">
                        <thead><tr><th>User</th><th>Email</th><th>Phone</th><th>Orders</th><th>Joined</th></tr></thead>
                        <tbody id="usersTable"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-modal" id="productModal">
        <div class="admin-modal-content">
            <h2 style="margin-bottom: 20px;">Add New Product</h2>
            <form onsubmit="saveProduct(event)">
                <div class="form-group"><label>Product Name</label><input type="text" id="pName" required></div>
                <div class="form-group">
                    <label>Category</label>
                    <select id="pCategory">
                        <option value="Electronics">Electronics</option>
                        <option value="Accessories">Accessories</option>
                        <option value="Home">Home</option>
                        <option value="Fashion">Fashion</option>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group"><label>Price (₹)</label><input type="number" id="pPrice" required></div>
                    <div class="form-group"><label>Old Price (₹)</label><input type="number" id="pOldPrice" required></div>
                </div>
                <div class="form-group"><label>Stock</label><input type="number" id="pStock" value="50"></div>
                <div class="form-group"><label>Image URL</label><input type="url" id="pImage" required></div>
                <div class="form-group"><label>Badge Text</label><input type="text" id="pBadge" placeholder="e.g., 70% OFF"></div>
                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <button type="submit" class="admin-btn">💾 Save Product</button>
                    <button type="button" class="admin-btn secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
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
        } catch (e) { currentUser = null; }
        
        if (!currentUser || !currentUser.isAdmin) {
            window.location.href = 'index.php';
        }

        document.getElementById('adminName').textContent = currentUser.name;

        function showSection(section) {
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.admin-nav-item').forEach(n => n.classList.remove('active'));
            event.target.closest('.admin-nav-item').classList.add('active');
            document.getElementById(section).classList.add('active');
            if (section === 'products') renderProducts();
            if (section === 'orders') renderOrders();
            if (section === 'users') renderUsers();
        }

        function refreshDashboard() {
            fetch('admin-api.php?action=stats')
                .then(r => r.json())
                .then(d => {
                    document.getElementById('totalRevenue').textContent = '₹' + (d.revenue || 0).toLocaleString();
                    document.getElementById('totalOrders').textContent = d.orders || 0;
                    document.getElementById('totalUsers').textContent = d.users || 0;
                    document.getElementById('totalProducts').textContent = d.items || 0;
                });
            
            fetch('admin-api.php?action=orders')
                .then(r => r.json())
                .then(d => {
                    const recent = (d.orders || []).slice(0, 5);
                    document.getElementById('recentOrders').innerHTML = recent.map(o => `
                        <tr><td>#${o.order_id.slice(-6)}</td><td>${o.delivery_name || 'Guest'}</td>
                        <td>${new Date(o.ordered_date).toLocaleDateString()}</td>
                        <td>₹${parseFloat(o.total).toLocaleString()}</td>
                        <td><span class="admin-status ${o.status.toLowerCase()}">${o.status}</span></td></tr>
                    `).join('') || '<tr><td colspan="5" style="text-align:center;">No orders</td></tr>';
                });
            showToast('Dashboard refreshed!', 'success');
        }

        function renderProducts() {
            fetch('admin-api.php?action=products')
                .then(r => r.json())
                .then(d => {
                    const products = d.products || [];
                    document.getElementById('productsTable').innerHTML = products.map(p => `
                        <tr>
                            <td><div style="display:flex;align-items:center;gap:15px;"><img src="${p.image}" style="width:50px;height:50px;border-radius:10px;object-fit:cover;" onerror="this.src='https://via.placeholder.com/50'"><div><strong>${p.name}</strong><br><small>ID: #${p.id}</small></div></div></td>
                            <td><span style="background:#f1f5f9;padding:6px 12px;border-radius:20px;">${p.category}</span></td>
                            <td><strong>₹${p.price}</strong></td>
                            <td>${p.stock}</td>
                            <td><button class="admin-action-btn delete" onclick="deleteProduct(${p.id})">🗑️</button></td>
                        </tr>
                    `).join('') || '<tr><td colspan="5" style="text-align:center;">No products</td></tr>';
                });
        }

        function openProductModal() { document.getElementById('productModal').classList.add('show'); }
        function closeModal() { document.getElementById('productModal').classList.remove('show'); }

        function saveProduct(e) {
            e.preventDefault();
            const price = parseInt(document.getElementById('pPrice').value);
            const oldPrice = parseInt(document.getElementById('pOldPrice').value);
            const discount = Math.round(((oldPrice - price) / oldPrice) * 100);
            
            const product = {
                name: document.getElementById('pName').value,
                category: document.getElementById('pCategory').value,
                price: price,
                oldPrice: oldPrice,
                image: document.getElementById('pImage').value,
                badge: document.getElementById('pBadge').value || `${discount}% OFF`,
                discount: discount,
                stock: parseInt(document.getElementById('pStock').value) || 50
            };
            
            fetch('admin-api.php?action=add_product', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(product)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    closeModal();
                    renderProducts();
                    showToast('Product added!', 'success');
                    e.target.reset();
                } else {
                    showToast('Error: ' + data.error, 'error');
                }
            });
        }

        function deleteProduct(id) {
            if (!confirm('Delete this product?')) return;
            fetch('admin-api.php?action=delete_product', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: id })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) { renderProducts(); showToast('Product deleted!', 'success'); }
                else showToast('Error', 'error');
            });
        }

        function renderOrders() {
            fetch('admin-api.php?action=orders')
                .then(r => r.json())
                .then(d => {
                    const orders = d.orders || [];
                    document.getElementById('ordersTable').innerHTML = orders.map(o => `
                        <tr>
                            <td>#${o.order_id.slice(-6)}</td>
                            <td><strong>${o.delivery_name || 'Guest'}</strong><br><small>${o.delivery_email || ''}</small></td>
                            <td>${o.items ? o.items.length : 0} items</td>
                            <td><strong>₹${parseFloat(o.total).toLocaleString()}</strong></td>
                            <td>
                                <select onchange="updateStatus('${o.order_id}', this.value)" style="padding:8px;border-radius:8px;">
                                    <option ${o.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                    <option ${o.status === 'Confirmed' ? 'selected' : ''}>Confirmed</option>
                                    <option ${o.status === 'Processing' ? 'selected' : ''}>Processing</option>
                                    <option ${o.status === 'Shipped' ? 'selected' : ''}>Shipped</option>
                                    <option ${o.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
                                    <option ${o.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select>
                            </td>
                            <td><button class="admin-action-btn delete" onclick="deleteOrder('${o.order_id}')">🗑️</button></td>
                        </tr>
                    `).join('') || '<tr><td colspan="6" style="text-align:center;">No orders</td></tr>';
                });
        }

        function refreshOrders() { renderOrders(); showToast('Orders refreshed!', 'success'); }
        
        function updateStatus(orderId, status) {
            fetch('admin-api.php?action=update_order_status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId, status: status })
            })
            .then(r => r.json())
            .then(data => { if (data.success) showToast('Status updated!', 'success'); });
        }

        function deleteOrder(orderId) {
            if (!confirm('Delete this order?')) return;
            fetch('admin-api.php?action=delete_order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: orderId })
            })
            .then(r => r.json())
            .then(data => { if (data.success) { renderOrders(); showToast('Order deleted!', 'success'); } });
        }

        function renderUsers() {
            fetch('admin-api.php?action=users')
                .then(r => r.json())
                .then(d => {
                    const users = d.users || [];
                    document.getElementById('usersTable').innerHTML = users.map(u => `
                        <tr>
                            <td><div style="display:flex;align-items:center;gap:15px;"><div style="width:40px;height:40px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;">${(u.name || 'U').charAt(0).toUpperCase()}</div><div><strong>${u.name}</strong><br><small>ID: #${u.id}</small></div></div></td>
                            <td>${u.email}</td>
                            <td>${u.phone || 'N/A'}</td>
                            <td>${u.order_count || 0}</td>
                            <td>${new Date(u.created_at).toLocaleDateString()}</td>
                        </tr>
                    `).join('') || '<tr><td colspan="5" style="text-align:center;">No users</td></tr>';
                });
        }

        function exportOrders() {
            fetch('admin-api.php?action=orders')
                .then(r => r.json())
                .then(d => {
                    const orders = d.orders || [];
                    if (orders.length === 0) { showToast('No orders to export!', 'error'); return; }
                    let csv = 'Order ID,Customer,Email,Total,Status,Date\n';
                    orders.forEach(o => { csv += `${o.order_id},"${o.delivery_name}","${o.delivery_email}",${o.total},${o.status},"${new Date(o.ordered_date).toLocaleDateString()}"\n`; });
                    const blob = new Blob([csv], { type: 'text/csv' });
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(blob);
                    a.download = `orders_${new Date().toISOString().split('T')[0]}.csv`;
                    a.click();
                    showToast('Orders exported!', 'success');
                });
        }

        function logout() {
            if (confirm('Logout?')) {
                document.cookie = "currentUser=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                window.location.href = 'index.php';
            }
        }

        function showToast(msg, type = 'success') {
            const existing = document.querySelector('.toast');
            if (existing) existing.remove();
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.innerHTML = type === 'success' ? '✅ ' + msg : '❌ ' + msg;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        document.getElementById('productModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
        refreshDashboard();
    </script>
</body>
</html>