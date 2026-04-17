<?php
include 'db.php';

// Check if user is logged in
if (!isset($_COOKIE['currentUser'])) {
    header('Location: index.php');
    exit;
}

$user = json_decode($_COOKIE['currentUser'], true);
if ($user['isAdmin']) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - SastaBazaar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root { --primary: #f97316; --primary-dark: #ea580c; --secondary: #16a34a; --dark: #1e293b; --gray: #64748b; --danger: #ef4444; }
        
        body { background: #f8fafc; }
        
        .navbar {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0 50px;
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
        
        .nav-icons { display: flex; gap: 20px; align-items: center; }
        
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
        
        .user-menu { position: relative; }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            cursor: pointer;
            font-size: 20px;
        }
        
        .dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 60px;
            background: white;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            border-radius: 20px;
            min-width: 240px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            z-index: 1001;
        }
        
        .dropdown.show { display: block; }
        
        .dropdown-header {
            padding: 20px;
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border-bottom: 1px solid #fed7aa;
        }
        
        .dropdown-header-name { font-weight: 800; color: var(--dark); }
        .dropdown-header-email { color: var(--gray); font-size: 12px; margin-top: 5px; }
        
        .dropdown-item {
            padding: 15px 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 600;
            color: var(--dark);
            text-decoration: none;
        }
        
        .dropdown-item:hover { background: #fff7ed; color: var(--primary); }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 80px 50px;
            text-align: center;
        }
        
        .hero-content h1 { font-size: 52px; margin-bottom: 20px; font-weight: 900; }
        .hero-content p { font-size: 22px; margin-bottom: 35px; opacity: 0.95; }
        
        .btn-hero {
            background: white;
            color: var(--primary);
            padding: 18px 45px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 18px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .section { padding: 70px 50px; max-width: 1400px; margin: 0 auto; }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
        }
        
        .section-title { font-size: 42px; font-weight: 900; color: var(--dark); }
        
        .btn-view-all {
            padding: 14px 30px;
            background: #fff7ed;
            color: var(--primary);
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }
        
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
        
        /* Search Results Section */
        .search-results-section { display: none; }
        .search-results-section.show { display: block; }
        
        .no-search-results {
            text-align: center;
            padding: 50px;
            display: none;
        }
        .no-search-results h3 { color: var(--dark); margin-bottom: 10px; }
        .no-search-results p { color: var(--gray); }
        
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .nav-links { display: none; }
            .nav-search { max-width: 200px; }
            .nav-search-input { padding: 10px 40px 10px 15px; font-size: 13px; }
            .hero-content h1 { font-size: 32px; }
            .section { padding: 40px 20px; }
            .section-title { font-size: 28px; }
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
                <a href="home.php" class="nav-link active">Home</a>
                <a href="product.php" class="nav-link">Products</a>
                <a href="orders.php" class="nav-link">My Orders</a>
            </div>
            
            <div class="nav-icons">
                <a href="cart.php" class="icon-btn">
                    🛒
                    <span class="badge" id="cartBadge">0</span>
                </a>
                
                <div class="user-menu">
                    <div class="user-avatar" id="userAvatarBtn" onclick="toggleDropdown()">U</div>
                    <div class="dropdown" id="userDropdown">
                        <div class="dropdown-header">
                            <div class="dropdown-header-name" id="dropdownName">User</div>
                            <div class="dropdown-header-email" id="dropdownEmail">user@email.com</div>
                        </div>
                        <a href="profile.php" class="dropdown-item">👤 My Profile</a>
                        <a href="orders.php" class="dropdown-item">📦 My Orders</a>
                        <a href="cart.php" class="dropdown-item">🛒 My Cart</a>
                        <div style="height: 1px; background: #e2e8f0; margin: 8px 0;"></div>
                        <a href="#" class="dropdown-item" onclick="logout()">🚪 Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="hero-content">
            <h1>Welcome to SastaBazaar!</h1>
            <p>Get the best deals on top brands. Up to 70% off today!</p>
            <a href="product.php" class="btn-hero">Start Shopping</a>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h2 class="section-title">🔥 Flash Sale</h2>
            <a href="product.php" class="btn-view-all">View All</a>
        </div>
        <div class="products-grid" id="featuredProducts"></div>
        
        <!-- Search Results Section -->
        <div class="search-results-section" id="searchResultsSection">
            <div class="section-header" style="margin-top: 50px;">
                <h2 class="section-title">🔍 Search Results</h2>
                <button class="btn-view-all" onclick="clearNavSearch()">Clear Search</button>
            </div>
            <div class="products-grid" id="searchResultsGrid"></div>
            <div class="no-search-results" id="noSearchResults">
                <h3>No products found</h3>
                <p>Try different keywords</p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>© 2026 SastaBazaar. Sabse Sasta, Sabse Achha!</p>
        <p>Shop with confidence — fast delivery, easy returns, and 24/7 support.</p>
    </footer>

    <script>
        let currentUser = null;
        try {
            const cookieName = 'currentUser';
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const [name, value] = cookie.trim().split('=');
                if (name === cookieName) {
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
        
        if (currentUser.isAdmin) {
            window.location.href = 'admin.php';
        }

        document.getElementById('userAvatarBtn').textContent = currentUser.name.charAt(0).toUpperCase();
        document.getElementById('dropdownName').textContent = currentUser.name;
        document.getElementById('dropdownEmail').textContent = currentUser.email;

        let products = [];

        function loadProducts() {
            fetch('api.php?action=products')
                .then(response => response.json())
                .then(data => {
                    products = data.products || [];
                    renderFeaturedProducts();
                })
                .catch(error => {
                    console.warn('Failed to load products:', error);
                    products = [];
                    renderFeaturedProducts();
                });
        }

        function renderFeaturedProducts() {
            const featured = products.slice(0, 4);
            document.getElementById('featuredProducts').innerHTML = featured.map(p => `
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

        // Header Search Functions
        function handleNavSearch(event) {
            if (event.key === 'Enter') {
                performNavSearch();
            }
        }

        function performNavSearch() {
            const searchTerm = document.getElementById('navSearchInput').value.trim().toLowerCase();
            const searchResultsSection = document.getElementById('searchResultsSection');
            const searchResultsGrid = document.getElementById('searchResultsGrid');
            const noSearchResults = document.getElementById('noSearchResults');
            const featuredSection = document.getElementById('featuredProducts').parentElement;
            
            if (searchTerm === '') {
                searchResultsSection.classList.remove('show');
                featuredSection.style.display = 'block';
                return;
            }
            
            const filtered = products.filter(p => 
                p.name.toLowerCase().includes(searchTerm) || 
                (p.category && p.category.toLowerCase().includes(searchTerm)) ||
                (p.brand && p.brand.toLowerCase().includes(searchTerm))
            );
            
            searchResultsSection.classList.add('show');
            
            if (filtered.length === 0) {
                searchResultsGrid.innerHTML = '';
                noSearchResults.style.display = 'block';
            } else {
                noSearchResults.style.display = 'none';
                searchResultsGrid.innerHTML = filtered.map(p => `
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
            
            // Scroll to results
            searchResultsSection.scrollIntoView({ behavior: 'smooth' });
        }

        function clearNavSearch() {
            document.getElementById('navSearchInput').value = '';
            document.getElementById('searchResultsSection').classList.remove('show');
        }

        function addToCart(productId) {
            const product = products.find(p => p.id === productId);
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
            const badge = document.getElementById('cartBadge');
            if (badge) badge.textContent = count;
        }

        function toggleDropdown() {
            document.getElementById('userDropdown').classList.toggle('show');
        }

        function logout() {
            document.cookie = "currentUser=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            window.location.href = 'index.php';
        }

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                document.getElementById('userDropdown').classList.remove('show');
            }
        });

        loadProducts();
        updateCartBadge();
    </script>
</body>
</html>