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
    <title>Profile - SastaBazaar</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root { --primary: #f97316; --primary-dark: #ea580c; --dark: #1e293b; --gray: #64748b; }
        
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
        
        .profile-page { max-width: 800px; margin: 0 auto; padding: 50px 20px; }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px;
            border-radius: 30px;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 48px;
            font-weight: 900;
            color: var(--primary);
        }
        
        .profile-name { font-size: 32px; font-weight: 900; margin-bottom: 10px; }
        .profile-email { opacity: 0.9; }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .stat-value { font-size: 36px; font-weight: 900; color: var(--primary); margin-bottom: 10px; }
        .stat-label { color: var(--gray); font-weight: 600; }
        
        .profile-form {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .form-title { font-size: 24px; font-weight: 900; margin-bottom: 30px; color: var(--dark); }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: var(--dark); }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
        }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); }
        
        .btn-save {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .navbar { padding: 0 20px; }
            .profile-stats { grid-template-columns: 1fr; }
            .profile-header { padding: 40px; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="nav-container">
            <a href="home.php" class="nav-logo">🛒 SastaBazaar</a>
            <a href="home.php" style="color: var(--gray); text-decoration: none;">← Back</a>
        </div>
    </nav>

    <div class="profile-page">
        <div class="profile-header">
            <div class="profile-avatar" id="userAvatar">U</div>
            <h2 class="profile-name" id="profileName">User Name</h2>
            <p class="profile-email" id="profileEmail">user@email.com</p>
        </div>

        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-value" id="orderCount">0</div>
                <div class="stat-label">Total Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="memberSince">2024</div>
                <div class="stat-label">Member Since</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="savings">₹0</div>
                <div class="stat-label">Total Savings</div>
            </div>
        </div>

        <div class="profile-form">
            <h3 class="form-title">Edit Profile</h3>
            <form onsubmit="updateProfile(event)">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="editName" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" id="editPhone" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="editEmail" readonly style="background: #f1f5f9;">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea id="editAddress" rows="3" placeholder="Enter your full address"></textarea>
                </div>
                <button type="submit" class="btn-save">Save Changes</button>
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
        } catch (e) {
            currentUser = null;
        }
        
        if (!currentUser) {
            window.location.href = 'index.php';
        }

        document.getElementById('userAvatar').textContent = currentUser.name.charAt(0).toUpperCase();
        document.getElementById('profileName').textContent = currentUser.name;
        document.getElementById('profileEmail').textContent = currentUser.email;
        document.getElementById('editName').value = currentUser.name;
        document.getElementById('editPhone').value = currentUser.phone || '';
        document.getElementById('editEmail').value = currentUser.email;
        document.getElementById('editAddress').value = currentUser.address || '';
        document.getElementById('memberSince').textContent = new Date().getFullYear();

        fetch('my-orders.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('orderCount').textContent = data.orders.length;
                }
            });

        function updateProfile(e) {
            e.preventDefault();
            
            const updatedUser = {
                ...currentUser,
                name: document.getElementById('editName').value,
                phone: document.getElementById('editPhone').value,
                address: document.getElementById('editAddress').value
            };
            
            fetch('update-profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updatedUser)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.cookie = "currentUser=" + encodeURIComponent(JSON.stringify(updatedUser)) + "; path=/";
                    alert('✅ Profile updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>