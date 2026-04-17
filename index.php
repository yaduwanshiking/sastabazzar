<?php
include 'db.php';

$loginError = '';
$signupError = '';
$signupSuccess = '';
$activeTab = 'login';

// Check if already logged in
if (isset($_COOKIE['currentUser'])) {
    $user = json_decode($_COOKIE['currentUser'], true);
    if ($user && isset($user['isAdmin'])) {
        if ($user['isAdmin']) {
            header('Location: admin.php');
            exit;
        } else {
            header('Location: home.php');
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $activeTab = 'login';
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $loginError = 'Please provide both email and password.';
        } else {
            $stmt = $conn->prepare('SELECT id, name, email, phone, password, isAdmin, address FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    $user = [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'phone' => $row['phone'],
                        'address' => $row['address'],
                        'isAdmin' => (bool)$row['isAdmin']
                    ];
                    setcookie('currentUser', json_encode($user), time() + (86400 * 30), "/");
                    $_SESSION['user_id'] = $row['id'];
                    
                    if ($user['isAdmin']) {
                        header('Location: admin.php');
                        exit;
                    }
                    header('Location: home.php');
                    exit;
                }
            }
            $loginError = 'Invalid email or password.';
            $stmt->close();
        }
    } elseif ($action === 'signup') {
        $activeTab = 'signup';
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $phone === '' || $password === '') {
            $signupError = 'Please complete all fields.';
        } elseif (strlen($password) < 4) {
            $signupError = 'Password must be at least 4 characters.';
        } else {
            $check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $check->bind_param('s', $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $signupError = 'Email already registered, please login.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insert = $conn->prepare('INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)');
                $insert->bind_param('ssss', $name, $email, $phone, $hashedPassword);

                if ($insert->execute()) {
                    $signupSuccess = 'Account created successfully. Please login.';
                    $activeTab = 'login';
                } else {
                    $signupError = 'Unable to create account: ' . $conn->error;
                }
                $insert->close();
            }
            $check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SastaBazaar - Login</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        :root { --primary: #f97316; --primary-dark: #ea580c; --danger: #ef4444; --dark: #1e293b; --gray: #64748b; }
        
        body { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); min-height: 100vh; }
        
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-card {
            background: white;
            border-radius: 30px;
            width: 100%;
            max-width: 480px;
            padding: 50px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .logo {
            text-align: center;
            font-size: 48px;
            font-weight: 900;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .tagline {
            text-align: center;
            color: var(--gray);
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .tabs {
            display: flex;
            background: #f1f5f9;
            padding: 5px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            border-radius: 12px;
            font-weight: 800;
            transition: all 0.3s;
        }
        
        .tab.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .message {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
        }
        .message.success { background: #dcfce7; color: #166534; }
        .message.error { background: #fee2e2; color: #991b1b; }
        
        .form { display: none; }
        .form.active { display: block; }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--dark);
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .features {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .feature {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--gray);
        }
        
        @media (max-width: 480px) {
            .auth-card { padding: 30px; }
            .logo { font-size: 36px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <div class="logo">🛒 SastaBazaar</div>
            <div class="tagline">Sabse Sasta, Sabse Achha!</div>
            
            <div class="tabs">
                <div class="tab <?= $activeTab === 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Login</div>
                <div class="tab <?= $activeTab === 'signup' ? 'active' : '' ?>" onclick="switchTab('signup')">Sign Up</div>
            </div>
            
            <?php if ($loginError): ?>
                <div class="message error"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>
            <?php if ($signupError): ?>
                <div class="message error"><?= htmlspecialchars($signupError) ?></div>
            <?php endif; ?>
            <?php if ($signupSuccess): ?>
                <div class="message success"><?= htmlspecialchars($signupSuccess) ?></div>
            <?php endif; ?>
            
            <form class="form <?= $activeTab === 'login' ? 'active' : '' ?>" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn-submit">Sign In</button>
            </form>
            
            <form class="form <?= $activeTab === 'signup' ? 'active' : '' ?>" method="POST">
                <input type="hidden" name="action" value="signup">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Enter your name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" placeholder="Enter phone number" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Create password (min 4 characters)" required>
                </div>
                <button type="submit" class="btn-submit">Create Account</button>
            </form>
            
            <div class="features">
                <div class="feature">🚚 Free Delivery</div>
                <div class="feature">🔒 Secure Payment</div>
                <div class="feature">↩️ Easy Returns</div>
                <div class="feature">💰 Cash on Delivery</div>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            const tabs = document.querySelectorAll('.tab');
            const forms = document.querySelectorAll('.form');
            
            tabs.forEach(t => t.classList.remove('active'));
            forms.forEach(f => f.classList.remove('active'));
            
            if (tab === 'login') {
                tabs[0].classList.add('active');
                forms[0].classList.add('active');
            } else {
                tabs[1].classList.add('active');
                forms[1].classList.add('active');
            }
        }
    </script>
</body>
</html>