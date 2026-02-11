<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — MeetingKU</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Animated gradient background */
        .login-bg {
            position: fixed;
            inset: 0;
            background: linear-gradient(-45deg, #7c2d12, #9a3412, #ea580c, #f97316, #d97706, #c2410c);
            background-size: 400% 400%;
            animation: gradientShift 12s ease infinite;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating orbs */
        .login-bg::before,
        .login-bg::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            animation: floatOrb 8s ease-in-out infinite alternate;
        }
        .login-bg::before {
            width: 400px;
            height: 400px;
            top: -100px;
            right: -100px;
        }
        .login-bg::after {
            width: 300px;
            height: 300px;
            bottom: -80px;
            left: -80px;
            animation-delay: -4s;
        }
        @keyframes floatOrb {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(30px, -30px) scale(1.1); }
        }

        /* Grid pattern overlay */
        .grid-overlay {
            position: fixed;
            inset: 0;
            background-image: 
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
        }

        /* Login card */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            margin: 1rem;
            background: white;
            border-radius: 1.25rem;
            padding: 2.5rem;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255,255,255,0.1);
            animation: cardAppear 0.6s cubic-bezier(0.21, 1.02, 0.73, 1) forwards;
            opacity: 0;
        }
        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Logo & title */
        .login-logo {
            width: 3.5rem;
            height: 3.5rem;
            margin: 0 auto 1.25rem;
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        .login-title {
            text-align: center;
            background: linear-gradient(135deg, #ea580c, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            font-size: 1.75rem;
            letter-spacing: -0.025em;
            margin-bottom: 0.375rem;
        }
        .login-subtitle {
            text-align: center;
            color: #94a3b8;
            font-size: 0.875rem;
            font-weight: 400;
            margin-bottom: 2rem;
        }

        /* Input groups */
        .input-group-login {
            margin-bottom: 1.25rem;
        }
        .input-label {
            display: block;
            color: #374151;
            font-size: 0.8125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            letter-spacing: 0.01em;
        }
        .input-wrapper {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.875rem;
            pointer-events: none;
            transition: color 0.25s;
        }
        .login-input {
            width: 100%;
            padding: 0.75rem 0.875rem 0.75rem 2.75rem;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.625rem;
            color: #1e293b;
            font-size: 0.9375rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.25s;
            outline: none;
        }
        .login-input::placeholder {
            color: #94a3b8;
        }
        .login-input:focus {
            border-color: #fb923c;
            background: white;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        .login-input:focus + .input-icon,
        .login-input:focus ~ .input-icon {
            color: #f97316;
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.25rem;
            font-size: 0.875rem;
            transition: color 0.2s;
        }
        .password-toggle:hover {
            color: #f97316;
        }

        /* Submit button */
        .login-submit {
            width: 100%;
            padding: 0.8125rem;
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
            border: none;
            border-radius: 0.625rem;
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: 0.9375rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            letter-spacing: 0.01em;
            box-shadow: 0 2px 8px rgba(234, 88, 12, 0.3);
        }
        .login-submit:hover {
            box-shadow: 0 4px 16px rgba(234, 88, 12, 0.4);
            transform: translateY(-1px);
        }
        .login-submit:active {
            transform: translateY(0);
        }

        /* Alert boxes */
        .login-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.625rem;
            font-size: 0.8125rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
            animation: alertPop 0.3s ease-out;
        }
        .login-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .login-alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .login-alert-icon {
            width: 1.375rem;
            height: 1.375rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.6875rem;
            flex-shrink: 0;
            margin-top: 0.0625rem;
        }
        .login-alert-error .login-alert-icon { background: #ef4444; color: white; }
        .login-alert-success .login-alert-icon { background: #10b981; color: white; }
        @keyframes alertPop {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Footer text */
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #94a3b8;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="login-bg"></div>
    <div class="grid-overlay"></div>

    <div class="login-card">
        <div class="login-logo">
            <i class="fas fa-calendar-check"></i>
        </div>
        <h1 class="login-title">MeetingKU</h1>
        <p class="login-subtitle">Silakan login untuk melanjutkan</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="login-alert login-alert-error">
                <div class="login-alert-icon">
                    <i class="fas fa-exclamation"></i>
                </div>
                <div>
                    <?php 
                        $error = session()->getFlashdata('error');
                        if (is_string($error)) {
                            echo esc($error);
                        } else if (is_array($error)) {
                            echo '<ul style="margin:0;padding-left:1rem;">';
                            foreach ($error as $err) {
                                echo '<li>' . esc($err) . '</li>';
                            }
                            echo '</ul>';
                        }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="login-alert login-alert-success">
                <div class="login-alert-icon">
                    <i class="fas fa-check"></i>
                </div>
                <div><?= session()->getFlashdata('success') ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('auth/login') ?>" method="POST">
            <?= csrf_field() ?>
            
            <div class="input-group-login">
                <label for="username" class="input-label">Username</label>
                <div class="input-wrapper">
                    <input id="username" 
                           name="username" 
                           type="text" 
                           required 
                           class="login-input" 
                           placeholder="Masukkan username"
                           value="<?= old('username') ?>"
                           autocomplete="username">
                    <span class="input-icon"><i class="fas fa-user"></i></span>
                </div>
            </div>

            <div class="input-group-login">
                <label for="password" class="input-label">Password</label>
                <div class="input-wrapper">
                    <input id="password" 
                           name="password" 
                           type="password" 
                           required 
                           class="login-input" 
                           style="padding-right: 2.75rem;"
                           placeholder="Masukkan password"
                           autocomplete="current-password">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <button type="button" class="password-toggle" id="togglePassword" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="login-submit">
                <i class="fas fa-arrow-right"></i>
                Masuk
            </button>
        </form>

        <p class="login-footer">&copy; <?= date('Y') ?> MeetingKU — Sistem Manajemen Meeting</p>
    </div>

    <script>
        // Password visibility toggle
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>
</html>
