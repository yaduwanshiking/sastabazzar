<?php
if (!isset($_COOKIE['currentUser'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - SastaBazaar</title>
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
        
        .cart-page { max-width: 1200px; margin: 0 auto; padding: 50px 20px; }
        .page-title { font-size: 42px; font-weight: 900; margin-bottom: 40px; color: var(--dark); }
        
        .cart-layout { display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; }
        
        .cart-box {
            background: white;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .cart-item { display: flex; gap: 25px; padding: 25px 0; border-bottom: 2px solid #f1f5f9; align-items: center; flex-wrap: wrap; }
        .cart-item-image { width: 120px; height: 120px; border-radius: 16px; overflow: hidden; }
        .cart-item-image img { width: 100%; height: 100%; object-fit: cover; }
        
        .cart-item-details { flex: 1; min-width: 200px; }
        .cart-item-name { font-size: 20px; font-weight: 800; margin-bottom: 8px; color: var(--dark); }
        .cart-item-price { color: var(--primary); font-size: 24px; font-weight: 900; }
        
        .quantity-control { display: flex; align-items: center; gap: 15px; margin-top: 15px; }
        .qty-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 12px;
            cursor: pointer;
            font-size: 20px;
            font-weight: 700;
        }
        .qty-value { font-size: 18px; font-weight: 800; min-width: 40px; text-align: center; }
        
        .cart-item-total { text-align: right; min-width: 150px; }
        .cart-item-total-price { font-size: 24px; font-weight: 900; color: var(--dark); margin-bottom: 10px; }
        
        .btn-remove {
            background: #fee2e2;
            color: var(--danger);
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
        }
        
        .cart-summary {
            background: white;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .summary-title { font-size: 24px; font-weight: 900; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #f1f5f9; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 17px; color: var(--gray); }
        .summary-total { display: flex; justify-content: space-between; font-size: 28px; font-weight: 900; color: var(--dark); padding-top: 25px; border-top: 3px solid #f1f5f9; margin-top: 25px; }
        
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
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
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
            .cart-layout { grid-template-columns: 1fr; }
            .cart-item { flex-direction: column; text-align: center; }
            .cart-item-total { text-align: center; }
            .navbar { padding: 0 20px; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="home.php" class="nav-logo">🛒 SastaBazaar</a>
            <a href="home.php" style="color: var(--gray); text-decoration: none;">← Continue Shopping</a>
        </div>
    </nav>

    <div class="cart-page">
        <h1 class="page-title">🛒 Shopping Cart</h1>
        <div class="cart-layout">
            <div class="cart-box" id="cartItemsContainer"></div>
            
            <div class="cart-summary" id="cartSummary" style="display: none;">
                <div class="summary-title">Order Summary</div>
                <div class="summary-row"><span>Subtotal</span><span id="cartSubtotal">₹0</span></div>
                <div class="summary-row"><span>Shipping</span><span style="color: var(--secondary); font-weight: 700;">FREE</span></div>
                <div class="summary-row"><span>Tax (18% GST)</span><span id="cartTax">₹0</span></div>
                <div class="summary-total"><span>Total</span><span id="cartTotal">₹0</span></div>
                <a href="checkout.php" class="btn-checkout">Proceed to Checkout →</a>
            </div>
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

        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        function renderCart() {
            if (cart.length === 0) {
                document.getElementById('cartItemsContainer').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">🛒</div>
                        <h3>Your cart is empty</h3>
                        <p>Add some products to get started!</p>
                        <a href="product.php" class="btn-hero">Continue Shopping</a>
                    </div>
                `;
                document.getElementById('cartSummary').style.display = 'none';
                return;
            }

            document.getElementById('cartItemsContainer').innerHTML = cart.map((item, index) => `
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="${item.image}" alt="${item.name}" onerror="this.src='https://via.placeholder.com/120'">
                    </div>
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.name}</div>
                        <div class="cart-item-price">₹${item.price}</div>
                        <div class="quantity-control">
                            <button class="qty-btn" onclick="updateQty(${index}, -1)">−</button>
                            <span class="qty-value">${item.quantity}</span>
                            <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                        </div>
                    </div>
                    <div class="cart-item-total">
                        <div class="cart-item-total-price">₹${item.price * item.quantity}</div>
                        <button class="btn-remove" onclick="removeItem(${index})">Remove</button>
                    </div>
                </div>
            `).join('');

            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = Math.round(subtotal * 0.18);
            const total = subtotal + tax;

            document.getElementById('cartSubtotal').textContent = '₹' + subtotal;
            document.getElementById('cartTax').textContent = '₹' + tax;
            document.getElementById('cartTotal').textContent = '₹' + total;
            document.getElementById('cartSummary').style.display = 'block';
        }

        function updateQty(index, change) {
            cart[index].quantity += change;
            if (cart[index].quantity <= 0) cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }

        function removeItem(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            renderCart();
        }

        renderCart();
    </script>
</body>
</html>