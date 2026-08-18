<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Shree Giriraj Poly Plast</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --accent: #10b981;
            --sidebar-bg: #0f172a;
            --bg: #f0f2f8;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --danger: #ef4444;
            --shadow-lg: 0 20px 60px rgba(0,0,0,.18);
            --radius: 16px;
        }

        html, body {
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--sidebar-bg);
            overflow: hidden;
        }

        /* Animated background */
        .bg-canvas {
            position: fixed; inset: 0; z-index: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        }
        .bg-canvas::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 30%, rgba(99,102,241,.18) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 80% 70%, rgba(16,185,129,.12) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 50% 0%, rgba(139,92,246,.10) 0%, transparent 60%);
        }
        .orb {
            position: absolute; border-radius: 50%;
            filter: blur(80px); opacity: .25;
            animation: float 8s ease-in-out infinite;
        }
        .orb-1 { width: 400px; height: 400px; background: #6366f1; top: -100px; left: -100px; animation-delay: 0s; }
        .orb-2 { width: 300px; height: 300px; background: #10b981; bottom: -80px; right: -80px; animation-delay: 3s; }
        .orb-3 { width: 200px; height: 200px; background: #8b5cf6; top: 40%; left: 60%; animation-delay: 5s; }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -30px) scale(1.05); }
        }

        /* Grid dots overlay */
        .grid-overlay {
            position: fixed; inset: 0; z-index: 0;
            background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* Main layout */
        .login-wrapper {
            position: relative; z-index: 10;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }

        .login-box {
            width: 100%; max-width: 440px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.10);
            backdrop-filter: blur(24px);
            border-radius: 24px;
            padding: 48px 44px;
            box-shadow: 0 32px 80px rgba(0,0,0,.40), 0 0 0 1px rgba(255,255,255,.05);
            animation: slideUp .5s cubic-bezier(.16,1,.3,1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(32px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Brand */
        .brand {
            display: flex; flex-direction: column; align-items: center;
            margin-bottom: 36px; text-align: center;
        }
        .brand-icon {
            width: 68px; height: 68px; border-radius: 18px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; color: #fff; margin-bottom: 18px;
            box-shadow: 0 8px 32px rgba(99,102,241,.4), 0 0 0 4px rgba(99,102,241,.15);
            position: relative;
        }
        .brand-icon::after {
            content: '';
            position: absolute; inset: -3px; border-radius: 21px;
            background: linear-gradient(135deg, rgba(99,102,241,.5), rgba(139,92,246,.5));
            z-index: -1; filter: blur(8px);
        }
        .brand-name {
            font-size: 20px; font-weight: 700; color: #fff; letter-spacing: -.3px;
            line-height: 1.2; margin-bottom: 4px;
        }
        .brand-sub {
            font-size: 12.5px; color: rgba(255,255,255,.45); font-weight: 400;
            letter-spacing: .3px; text-transform: uppercase;
        }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 12px; margin-bottom: 28px;
        }
        .divider-line { flex: 1; height: 1px; background: rgba(255,255,255,.10); }
        .divider-text { font-size: 12px; color: rgba(255,255,255,.35); font-weight: 500; white-space: nowrap; }

        /* Form */
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block; font-size: 13px; font-weight: 500;
            color: rgba(255,255,255,.65); margin-bottom: 8px; letter-spacing: .1px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,.3); font-size: 14px; pointer-events: none;
            transition: color .2s;
        }
        .form-input {
            width: 100%; padding: 13px 14px 13px 40px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 12px; color: #fff; font-size: 14.5px;
            font-family: 'Inter', sans-serif; font-weight: 400;
            outline: none; transition: all .2s;
        }
        .form-input::placeholder { color: rgba(255,255,255,.25); }
        .form-input:focus {
            background: rgba(99,102,241,.08);
            border-color: rgba(99,102,241,.6);
            box-shadow: 0 0 0 3px rgba(99,102,241,.15);
        }
        .form-input:focus + .input-icon,
        .input-wrap:focus-within .input-icon { color: #818cf8; }

        /* Password toggle */
        .toggle-pass {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: rgba(255,255,255,.3); font-size: 14px; padding: 4px;
            transition: color .2s;
        }
        .toggle-pass:hover { color: rgba(255,255,255,.7); }

        /* Error */
        .error-alert {
            background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.25);
            border-radius: 10px; padding: 12px 14px; margin-bottom: 20px;
            display: flex; gap: 10px; align-items: flex-start;
        }
        .error-alert i { color: #ef4444; font-size: 14px; margin-top: 1px; flex-shrink: 0; }
        .error-alert span { font-size: 13px; color: rgba(255,255,255,.8); line-height: 1.5; }

        /* Remember row */
        .remember-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px;
        }
        .checkbox-wrap { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .checkbox-wrap input[type="checkbox"] { display: none; }
        .checkbox-custom {
            width: 18px; height: 18px; border-radius: 5px;
            border: 1.5px solid rgba(255,255,255,.2);
            background: rgba(255,255,255,.05);
            display: flex; align-items: center; justify-content: center;
            transition: all .2s; flex-shrink: 0;
        }
        .checkbox-wrap input:checked ~ .checkbox-custom {
            background: #6366f1; border-color: #6366f1;
        }
        .checkbox-wrap input:checked ~ .checkbox-custom::after {
            content: '✓'; font-size: 11px; color: #fff; font-weight: 700;
        }
        .checkbox-label { font-size: 13px; color: rgba(255,255,255,.55); }

        /* Submit button */
        .btn-login {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none; border-radius: 12px; color: #fff;
            font-size: 15px; font-weight: 600; font-family: 'Inter', sans-serif;
            cursor: pointer; letter-spacing: .2px;
            box-shadow: 0 8px 24px rgba(99,102,241,.35);
            transition: all .2s; position: relative; overflow: hidden;
        }
        .btn-login::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,.15), transparent);
            opacity: 0; transition: opacity .2s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 12px 32px rgba(99,102,241,.45); }
        .btn-login:hover::before { opacity: 1; }
        .btn-login:active { transform: translateY(0); }
        .btn-login i { margin-right: 8px; }

        /* Social Auth */
        .social-divider {
            display: flex; align-items: center; gap: 12px; margin: 24px 0 20px;
        }
        .social-divider-line { flex: 1; height: 1px; background: rgba(255,255,255,.08); }
        .social-divider-text { font-size: 11.5px; color: rgba(255,255,255,.35); text-transform: uppercase; letter-spacing: .5px; font-weight: 500; }

        .btn-social-google {
            display: flex; align-items: center; justify-content: center; gap: 12px;
            width: 100%; padding: 13px 18px; border-radius: 12px;
            font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif;
            text-decoration: none; color: #f1f5f9;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.12);
            backdrop-filter: blur(8px);
            transition: all .2s cubic-bezier(.16,1,.3,1);
            position: relative;
        }
        .btn-social-google:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,.09);
            border-color: rgba(234,67,53,.35);
            color: #ffffff;
            box-shadow: 0 8px 24px rgba(234,67,53,.18), 0 0 0 1px rgba(234,67,53,.25);
        }
        .btn-social-google:active { transform: translateY(0); }
        .btn-social-google svg {
            width: 19px; height: 19px; flex-shrink: 0;
        }

        /* Footer note */
        .login-footer {
            margin-top: 28px; text-align: center;
            font-size: 12px; color: rgba(255,255,255,.25);
        }
        .login-footer strong { color: rgba(255,255,255,.45); }
    </style>
</head>
<body>
<div class="bg-canvas">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>
<div class="grid-overlay"></div>

<div class="login-wrapper">
    <div class="login-box">
        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">
                <i class="fa-solid fa-industry"></i>
            </div>
            <div class="brand-name">Shree Giriraj Poly Plast</div>
            <div class="brand-sub">ERP Management System</div>
        </div>

        <!-- Divider -->
        <div class="divider">
            <div class="divider-line"></div>
            <div class="divider-text">Sign in to continue</div>
            <div class="divider-line"></div>
        </div>

        <!-- Errors -->
        @if($errors->any())
        <div class="error-alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.attempt') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-envelope input-icon"></i>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="you@company.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-pass" onclick="togglePassword()" id="toggleBtn" title="Show/Hide Password">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <label class="checkbox-wrap" for="remember">
                    <input type="checkbox" id="remember" name="remember">
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-label">Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <i class="fa-solid fa-right-to-bracket"></i>
                Sign In
            </button>
        </form>

        <!-- Social Login Divider -->
        <div class="social-divider">
            <div class="social-divider-line"></div>
            <div class="social-divider-text">Or continue with</div>
            <div class="social-divider-line"></div>
        </div>

        <!-- Social Login Buttons -->
        <a href="{{ route('auth.social.redirect', ['provider' => 'google']) }}" class="btn-social-google" id="googleLoginBtn" title="Sign in with Google">
            <svg viewBox="0 0 24 24" width="19" height="19">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
            </svg>
            <span>Sign in with Google</span>
        </a>

        <div class="login-footer">
            &copy; {{ date('Y') }} <strong>Shree Giriraj Poly Plast</strong>. All rights reserved.
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

// Button loading state on submit
document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Signing in...';
    btn.disabled = true;
});
</script>
</body>
</html>
