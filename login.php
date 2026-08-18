<?php
require_once 'config/db.php';
require_once 'config/auth.php';

$error = '';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM users 
                WHERE (username = ? OR email = ?) AND (status = 'active' OR status IS NULL)
                LIMIT 1
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            $pwdField = !empty($user['password_hash']) ? $user['password_hash'] : ($user['password'] ?? '');

            if ($user && password_verify($password, $pwdField)) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'] ?? $user['email'];
                $_SESSION['full_name'] = !empty($user['full_name']) ? $user['full_name'] : (!empty($user['name']) ? $user['name'] : 'User');
                $_SESSION['role'] = $user['role'] ?? 'partner';

                // Log Activity
                logActivity($pdo, 'LOGIN', 'Authentication', "User '" . $_SESSION['full_name'] . "' logged in");

                header("Location: index.php");
                exit;
            } else {
                $error = 'Invalid username or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="manifest.json">
    <title>Login - Shree Giriraj Poly Plast ERP</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #312e81 100%);
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary, #4f46e5);
            letter-spacing: -0.5px;
        }

        .brand-logo span {
            color: #0f172a;
            font-weight: 400;
            font-size: 1rem;
            display: block;
            margin-top: 4px;
        }

        .login-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .login-subtitle {
            font-size: 0.875rem;
            color: #64748b;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.2rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .demo-accounts {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.8rem;
            color: #64748b;
            text-align: center;
        }

        .demo-accounts code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            color: #334155;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand-header">
        <div class="brand-logo">
            Giriraj ERP <span>Poly Plast ERP Solutions</span>
        </div>
        <div class="login-title">Sign In to Account</div>
        <div class="login-subtitle">Enter your credentials to manage ERP entries</div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-danger">
            <i class='bx bx-error-circle'></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <div class="input-wrapper">
                <i class='bx bx-user'></i>
                <input type="text" id="username" name="username" class="form-control" placeholder="e.g. fua or partner" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
                <i class='bx bx-lock-alt'></i>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <i class='bx bx-log-in-circle'></i> Sign In
        </button>
    </form>

    <div class="demo-accounts">
        <div>Default Accounts:</div>
        <div style="margin-top: 5px;">
            Partner 1: <code>fua</code> | Partner 2: <code>partner</code>
        </div>
        <div style="margin-top: 3px;">
            Password: <code>password123</code>
        </div>
    </div>
</div>

</body>
</html>
