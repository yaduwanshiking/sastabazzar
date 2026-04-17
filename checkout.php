<?php
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
    <title>Checkout - SastaBazaar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root { --primary: #f97316; --primary-dark: #ea580c; --secondary: #16a34a; --dark: #1e293b; --gray: #64748b; }
        
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
        
        .checkout-page { max-width: 1000px; margin: 0 auto; padding: 50px 20px; }
        .page-title { font-size: 42px; font-weight: 900; margin-bottom: 40px; color: var(--dark); }
        
        .checkout-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        
        .checkout-box {
            background: white;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .checkout-title { font-size: 24px; font-weight: 900; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #f1f5f9; }
        
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; margin-bottom: 10px; font-weight: 700; color: var(--dark); }
        .form-group input { width: 100%; padding: 18px; border: 2px solid #e2e8f0; border-radius: 15px; font-size: 16px; }
        .form-group input:focus { outline: none; border-color: var(--primary); }
        
        .payment-methods { display: flex; flex-direction: column; gap: 15px; }
        .payment-method {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s;
        }
        .payment-method:hover, .payment-method.selected { border-color: var(--primary); background: #fff7ed; }
        
        .preview-item { display: flex; gap: 15px; padding: 15px 0; border-bottom: 1px solid #f1f5f9; }
        .preview-item img { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; }
        .preview-details { flex: 1; }
        .preview-name { font-weight: 700; }
        .preview-price { color: var(--primary); font-weight: 800; }
        
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; color: var(--gray); }
        .summary-total { display: flex; justify-content: space-between; font-size: 24px; font-weight: 900; color: var(--dark); padding-top: 20px; border-top: 3px solid #f1f5f9; margin-top: 20px; }
        
        .btn-checkout {
            width: 100%;
            padding: 22px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 20px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 30px;
        }
        
        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .success-modal.show { display: flex; }
        
        .success-content {
            background: white;
            padding: 60px;
            border-radius: 30px;
            text-align: center;
            max-width: 500px;
        }
        
        .success-icon { width: 120px; height: 120px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 60px; margin: 0 auto 30px; }
        
        @media (max-width: 768px) {
            .checkout-grid { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
            .navbar { padding: 0 20px; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="home.php" class="nav-logo">🛒 SastaBazaar</a>
        </div>
    </nav>

    <div class="checkout-page">
        <h1 class="page-title">💳 Checkout</h1>
        <div class="checkout-grid">
            <div class="checkout-box">
                <div class="checkout-title">📍 Delivery Address</div>
                <form id="checkoutForm" onsubmit="placeOrder(event)">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="fname" value="<?= htmlspecialchars(explode(' ', $user['name'])[0] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="lname" value="<?= htmlspecialchars(explode(' ', $user['name'])[1] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" id="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" id="address" placeholder="Street address" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" id="city" required>
                        </div>
                        <div class="form-group">
                            <label>PIN Code</label>
                            <input type="text" id="pin" required>
                        </div>
                    </div>
                    
                    <div class="checkout-title" style="margin-top: 40px;">💳 Payment Method</div>
                    <div class="payment-methods">
                        <div class="payment-method selected" onclick="selectPayment(this)">
                            <div>💳</div>
                            <div>
                                <div style="font-weight: 800;">Credit/Debit Card</div>
                                <div style="font-size: 12px; color: var(--gray);">Visa, Mastercard, RuPay</div>
                            </div>
                        </div>
                        <div class="payment-method" onclick="selectPayment(this)">
                            <div>📱</div>
                            <div>
                                <div style="font-weight: 800;">UPI</div>
                                <div style="font-size: 12px; color: var(--gray);">Google Pay, PhonePe, Paytm</div>
                            </div>
                        </div>
                        <div class="payment-method" onclick="selectPayment(this)">
                            <div>💵</div>
                            <div>
                                <div style="font-weight: 800;">Cash on Delivery</div>
                                <div style="font-size: 12px; color: var(--gray);">Pay when you receive</div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-checkout">Place Order</button>
                </form>
            </div>
            
            <div class="checkout-box">
                <div class="checkout-title">🛒 Order Summary</div>
                <div id="orderItems"></div>
                <div class="summary-total">
                    <span>Total</span>
                    <span id="orderTotal">₹0</span>
                </div>
            </div>
        </div>
    </div>

    <div class="success-modal" id="successModal">
        <div class="success-content">
            <div class="success-icon">✅</div>
            <h2 style="font-size: 32px; font-weight: 900; margin-bottom: 15px;">Order Placed!</h2>
            <p style="color: var(--gray); margin-bottom: 30px;">Your order has been placed successfully.</p>
            <div style="background: #f1f5f9; padding: 20px; border-radius: 16px; margin-bottom: 30px;">
                <div>Order ID</div>
                <strong id="orderIdDisplay" style="color: var(--primary); font-size: 20px;"></strong>
            </div>
            <a href="orders.php" style="padding: 18px 40px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; border-radius: 14px; text-decoration: none; font-weight: 800; display: inline-block;">View My Orders</a>
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
        } catch (e) {
            currentUser = null;
        }
        
        if (!currentUser) {
            window.location.href = 'index.php';
        }

        const urlParams = new URLSearchParams(window.location.search);
        const buyNowId = urlParams.get('buy');
        
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        let orderItems = [];

        if (buyNowId) {
            fetch('api.php?action=product&id=' + buyNowId)
                .then(response => response.json())
                .then(product => {
                    if (product && product.id) {
                        orderItems = [{ ...product, quantity: 1 }];
                        renderOrderSummary();
                    }
                });
        } else {
            orderItems = [...cart];
            renderOrderSummary();
        }

        function renderOrderSummary() {
            const subtotal = orderItems.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0);
            const tax = Math.round(subtotal * 0.18);
            const total = subtotal + tax;
            
            document.getElementById('orderItems').innerHTML = orderItems.map(item => `
                <div class="preview-item">
                    <img src="${item.image}" alt="${item.name}" onerror="this.src='https://via.placeholder.com/60'">
                    <div class="preview-details">
                        <div class="preview-name">${item.name}</div>
                        <div class="preview-price">₹${item.price} x ${item.quantity || 1}</div>
                    </div>
                </div>
            `).join('');
            document.getElementById('orderTotal').textContent = '₹' + total;
        }

        function selectPayment(el) {
            document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
            el.classList.add('selected');
        }

        function placeOrder(e) {
            e.preventDefault();
            
            const subtotal = orderItems.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0);
            const tax = Math.round(subtotal * 0.18);
            const totalAmount = subtotal + tax;
            
            const order = {
                userId: currentUser.id,
                orderId: 'ORD' + Date.now(),
                items: orderItems.map(item => ({
                    id: item.id,
                    name: item.name,
                    price: item.price,
                    quantity: item.quantity || 1
                })),
                total: totalAmount,
                status: 'Confirmed',
                address: {
                    name: document.getElementById('fname').value + ' ' + document.getElementById('lname').value,
                    email: document.getElementById('email').value,
                    phone: document.getElementById('phone').value,
                    address: document.getElementById('address').value,
                    city: document.getElementById('city').value,
                    pincode: document.getElementById('pin').value
                }
            };

            fetch('save-order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(order)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (!buyNowId) {
                        localStorage.removeItem('cart');
                    }
                    document.getElementById('orderIdDisplay').textContent = order.orderId;
                    document.getElementById('successModal').classList.add('show');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error placing order. Please try again.');
            });
        }
    </script>
</body>
</html>