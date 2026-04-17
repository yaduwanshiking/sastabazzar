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
    <title>Products - SastaBazaar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root { --primary: #f97316; --primary-dark: #ea580c; --secondary: #16a34a; --dark: #1e293b; --gray: #64748b; --danger: #ef4444; }
        
        body { background: #f8fafc; }
        
        .navbar {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 0 50px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
            gap: 30px;
        }
        
        .nav-logo {
            font-size: 28px;
            font-weight: 900;
            color: var(--primary);
            text-decoration: none;
            white-space: nowrap;
        }
        
        /* Search in Header Styles */
        .nav-search {
            flex: 1;
            max-width: 500px;
            position: relative;
        }
        
        .nav-search-input {
            width: 100%;
            padding: 12px 45px 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }
        
        .nav-search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
        }
        
        .nav-search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .nav-search-btn:hover {
            background: var(--primary-dark);
        }
        
        .nav-links { display: flex; gap: 30px; }
        
        .nav-link {
            text-decoration: none;
            color: var(--gray);
            font-weight: 700;
            padding: 10px 20px;
            border-radius: 10px;
        }
        
        .nav-link:hover, .nav-link.active { color: var(--primary); background: #fff7ed; }
        
        .icon-btn {
            position: relative;
            cursor: pointer;
            font-size: 24px;
            padding: 10px;
            border-radius: 50%;
            background: #f1f5f9;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--dark);
        }
        
        .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
        }
        
        .products-page { max-width: 1400px; margin: 0 auto; padding: 40px 50px; }
        
        .page-header { margin-bottom: 40px; }
        .page-title { font-size: 42px; font-weight: 900; color: var(--dark); margin-bottom: 10px; }
        .page-subtitle { color: var(--gray); font-size: 18px; }
        
        .filters {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-select, .sort-select {
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }
        
        .sort-select { margin-left: auto; }
        
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 35px; }
        
        .product-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: all 0.4s;
        }
        
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 25px 60px rgba(0,0,0,0.15); }
        
        .product-image { height: 240px; position: relative; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s; }
        .product-card:hover .product-image img { transform: scale(1.15); }
        
        .product-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--danger);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
        }
        
        .product-info { padding: 28px; }
        .product-name { font-size: 20px; font-weight: 800; margin-bottom: 12px; color: var(--dark); }
        
        .product-price-row { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .product-price { font-size: 28px; font-weight: 900; color: var(--primary); }
        .product-old-price { font-size: 18px; color: var(--gray); text-decoration: line-through; }
        .product-discount { background: #dcfce7; color: var(--secondary); padding: 6px 14px; border-radius: 8px; font-size: 14px; font-weight: 800; }
        
        .product-buttons { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        
        .btn-buy, .btn-cart {
            padding: 16px;
            border: none;
            border-radius: 14px;
            font-weight: 800;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-buy { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: white; }
        .btn-cart { background: #f1f5f9; color: var(--dark); }
        .btn-cart:hover { background: var(--dark); color: white; }
        
        .footer {
            background: #1e293b;
            color: #e2e8f0;
            padding: 35px 50px;
            text-align: center;
        }
        .footer p { margin: 8px 0; opacity: 0.8; }
        .footer a { color: #f97316; text-decoration: none; font-weight: 700; }
        
        .no-results {
            text-align: center;
            padding: 100px 20px;
            display: none;
        }
        .no-results h3 { font-size: 28px; color: var(--dark); margin-bottom: 15px; }
        .no-results p { color: var(--gray); }
        
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .nav-links { display: none; }
            .nav-search { max-width: 200px; }
            .nav-search-input { padding: 10px 40px 10px 15px; font-size: 13px; }
            .products-page { padding: 30px 20px; }
            .page-title { font-size: 32px; }
            .filters { flex-direction: column; align-items: stretch; }
            .sort-select { margin-left: 0; }
            .products-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="home.php" class="nav-logo">🛒 SastaBazaar</a>
            
            <!-- Search Box in Header -->
            <div class="nav-search">
                <input type="text" class="nav-search-input" id="navSearchInput" placeholder="Search products..." onkeyup="handleNavSearch(event)">
                <button class="nav-search-btn" onclick="performNavSearch()">🔍</button>
            </div>
            
            <div class="nav-links">
                <a href="home.php" class="nav-link">Home</a>
                <a href="product.php" class="nav-link active">Products</a>
                <a href="orders.php" class="nav-link">My Orders</a>
            </div>
            
            <div class="nav-icons">
                <a href="cart.php" class="icon-btn">
                    🛒
                    <span class="badge" id="cartBadge">0</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="products-page">
        <div class="page-header">
            <h1 class="page-title">All Products</h1>
            <p class="page-subtitle">Discover amazing deals on all products</p>
        </div>
        
        <div class="filters">
            <div class="filter-group">
                <label>Category:</label>
                <select class="filter-select" id="categoryFilter" onchange="filterProducts()">
                    <option value="all">All Categories</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Home">Home</option>
                    <option value="Fashion">Fashion</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Price:</label>
                <select class="filter-select" id="priceFilter" onchange="filterProducts()">
                    <option value="all">All Prices</option>
                    <option value="0-500">Under ₹500</option>
                    <option value="500-1000">₹500 - ₹1000</option>
                    <option value="1000-2000">₹1000 - ₹2000</option>
                    <option value="2000+">Above ₹2000</option>
                </select>
            </div>
            <select class="sort-select" id="sortSelect" onchange="filterProducts()">
                <option value="default">Sort by: Featured</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
                <option value="discount">Biggest Discount</option>
            </select>
        </div>
        
        <div class="products-grid" id="productsGrid"></div>
        
        <div class="no-results" id="noResults">
            <h3>🔍 No products found</h3>
            <p>Try adjusting your search or filters</p>
        </div>
    </div>

    <footer class="footer">
        <p>© 2026 SastaBazaar. Sabse Sasta, Sabse Achha!</p>
        <p>Secure shopping with quick delivery and easy returns.</p>
    </footer>

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

        let allProducts = [];
        let currentSearchTerm = '';

        function loadProducts() {
            fetch('api.php?action=products')
                .then(response => response.json())
                .then(data => {
                    allProducts = data.products || [];
                    filterProducts();
                })
                .catch(error => {
                    console.warn('Failed to load products:', error);
                    allProducts = [];
                    filterProducts();
                });
        }

        // Header Search Functions
        function handleNavSearch(event) {
            if (event.key === 'Enter') {
                performNavSearch();
            }
        }

        function performNavSearch() {
            currentSearchTerm = document.getElementById('navSearchInput').value.trim().toLowerCase();
            filterProducts();
        }

        function filterProducts() {
            const category = document.getElementById('categoryFilter').value;
            const priceRange = document.getElementById('priceFilter').value;
            const sortBy = document.getElementById('sortSelect').value;
            
            let filtered = [...allProducts];
            
            // Apply header search filter
            if (currentSearchTerm) {
                filtered = filtered.filter(p => 
                    p.name.toLowerCase().includes(currentSearchTerm) || 
                    (p.category && p.category.toLowerCase().includes(currentSearchTerm)) ||
                    (p.brand && p.brand.toLowerCase().includes(currentSearchTerm))
                );
            }
            
            if (category !== 'all') {
                filtered = filtered.filter(p => p.category === category);
            }
            
            if (priceRange !== 'all') {
                if (priceRange === '2000+') {
                    filtered = filtered.filter(p => p.price >= 2000);
                } else {
                    const [min, max] = priceRange.split('-').map(Number);
                    filtered = filtered.filter(p => p.price >= min && p.price <= max);
                }
            }
            
            switch(sortBy) {
                case 'price-low':
                    filtered.sort((a, b) => a.price - b.price);
                    break;
                case 'price-high':
                    filtered.sort((a, b) => b.price - a.price);
                    break;
                case 'discount':
                    filtered.sort((a, b) => b.discount - a.discount);
                    break;
            }
            
            renderProducts(filtered);
        }
        
        function renderProducts(products) {
            const grid = document.getElementById('productsGrid');
            const noResults = document.getElementById('noResults');
            
            if (products.length === 0) {
                grid.innerHTML = '';
                noResults.style.display = 'block';
                return;
            }
            
            noResults.style.display = 'none';
            grid.innerHTML = products.map(p => `
                <div class="product-card">
                    <div class="product-image">
                        <img src="${p.image}" alt="${p.name}" onerror="this.src='https://via.placeholder.com/400x300?text=Product'">
                        <span class="product-badge">${p.badge || p.discount + '% OFF'}</span>
                    </div>
                    <div class="product-info">
                        <div class="product-name">${p.name}</div>
                        <div class="product-price-row">
                            <span class="product-price">₹${p.price}</span>
                            <span class="product-old-price">₹${p.old_price}</span>
                            <span class="product-discount">${p.discount}% off</span>
                        </div>
                        <div class="product-buttons">
                            <a href="checkout.php?buy=${p.id}" class="btn-buy">Buy Now</a>
                            <button class="btn-cart" onclick="addToCart(${p.id})">Add to Cart</button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
        
        function addToCart(productId) {
            const product = allProducts.find(p => p.id === productId);
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const existing = cart.find(item => item.id === productId);
            
            if (existing) {
                existing.quantity++;
            } else {
                cart.push({ ...product, quantity: 1 });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartBadge();
            alert('✅ Added to cart!');
        }
        
        function updateCartBadge() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartBadge').textContent = count;
        }
        
        loadProducts();
        updateCartBadge();
    </script>
</body>
</html>