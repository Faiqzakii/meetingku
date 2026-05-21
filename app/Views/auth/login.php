<?php
/**
 * Login — clean, fokus, aksen oranye.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — MeetingKU</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --canvas: #fffefb;
            --canvas-soft: #f8f4f0;
            --surface: #ffffff;
            --ink: #0f172a;
            --body: #475569;
            --mute: #94a3b8;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;
            --primary: #ff4f00;
            --primary-soft: #fff1ea;
            --primary-dark: #c2410c;
            --primary-ring: rgba(255, 79, 0, .35);
            --danger: #dc2626;
            --danger-soft: #fee2e2;
            --success: #16a34a;
            --success-soft: #dcfce7;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-1: 0 1px 2px rgba(15, 23, 42, .04);
            --shadow-3: 0 14px 30px rgba(15, 23, 42, .08), 0 4px 10px rgba(15, 23, 42, .04);
            --shadow-focus: 0 0 0 3px var(--primary-ring);
            --font-display: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
            --font-body: 'Inter', system-ui, -apple-system, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: var(--font-body);
            color: var(--ink);
            background:
                radial-gradient(circle at 18% 12%, rgba(255, 79, 0, .12), transparent 32rem),
                radial-gradient(circle at 82% 88%, rgba(255, 79, 0, .08), transparent 28rem),
                linear-gradient(180deg, var(--canvas) 0%, var(--canvas-soft) 100%);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }

        .login-shell { width: 100%; max-width: 420px; }

        .login-brand {
            display: inline-flex; align-items: center; gap: 10px;
            margin-bottom: 24px;
        }
        .login-brand-mark {
            width: 38px; height: 38px;
            background: var(--primary);
            color: #fff;
            border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1rem;
        }
        .login-brand-name {
            font-family: var(--font-display);
            font-weight: 800; font-size: 1.05rem; color: var(--ink);
            letter-spacing: -.01em;
        }

        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-3);
            padding: 32px;
        }
        .login-title {
            font-family: var(--font-display);
            font-size: 1.5rem; font-weight: 700;
            color: var(--ink); margin: 0 0 4px;
            letter-spacing: -.01em;
        }
        .login-subtitle {
            font-size: .9rem; color: var(--body); margin: 0 0 24px;
        }

        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 12px 14px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
            font-size: .85rem;
            margin-bottom: 18px;
        }
        .alert i { margin-top: 2px; }
        .alert-error  { background: var(--danger-soft);  border-color: #fecaca; color: #7f1d1d; }
        .alert-error i { color: var(--danger); }
        .alert-success { background: var(--success-soft); border-color: #bbf7d0; color: #14532d; }
        .alert-success i { color: var(--success); }
        .alert ul { margin: 0; padding-left: 18px; }

        .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
        .field-label {
            font-size: .8125rem; font-weight: 600; color: var(--ink);
        }
        .field-control { position: relative; }
        .field-control i.lead {
            position: absolute; top: 50%; left: 14px; transform: translateY(-50%);
            color: var(--mute); font-size: .9rem; pointer-events: none;
        }
        .field-control input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            min-height: 44px;
            background: var(--surface);
            color: var(--ink);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-md);
            font: inherit; font-size: .9rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-control input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: var(--shadow-focus);
        }
        .field-control input[type="password"],
        .field-control input.has-toggle { padding-right: 44px; }
        .password-toggle {
            position: absolute; top: 50%; right: 6px; transform: translateY(-50%);
            width: 32px; height: 32px;
            background: transparent; border: 0; color: var(--mute);
            border-radius: 8px; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .password-toggle:hover { background: var(--canvas-soft); color: var(--ink); }
        .password-toggle:focus-visible { outline: none; box-shadow: var(--shadow-focus); }

        .caps-hint {
            display: none;
            margin-top: 6px;
            font-size: .75rem; color: var(--warning, #d97706);
        }
        .caps-hint.is-on { display: inline-flex; align-items: center; gap: 6px; }

        .login-submit {
            width: 100%;
            min-height: 46px;
            padding: 12px 16px;
            background: var(--primary);
            color: #fff;
            border: 0;
            border-radius: var(--radius-md);
            font: inherit; font-size: .95rem; font-weight: 700;
            cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            transition: background .15s, transform .15s;
        }
        .login-submit:hover { background: var(--primary-dark); }
        .login-submit:active { transform: translateY(1px); }
        .login-submit:focus-visible { outline: none; box-shadow: var(--shadow-focus); }

        .login-footer {
            margin-top: 24px;
            text-align: center;
            font-size: .75rem; color: var(--mute);
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }

        .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }
    </style>
</head>
<body>
<main class="login-shell" role="main">
    <div class="login-brand" aria-label="MeetingKU">
        <span class="login-brand-mark"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
        <span class="login-brand-name">MeetingKU</span>
    </div>

    <section class="login-card" aria-labelledby="loginHeading">
        <h1 id="loginHeading" class="login-title">Selamat datang kembali</h1>
        <p class="login-subtitle">Masuk untuk mengelola agenda dan ruangan.</p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error" role="alert">
                <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                <div>
                    <?php
                        $error = session()->getFlashdata('error');
                        if (is_string($error)) {
                            echo esc($error);
                        } elseif (is_array($error)) {
                            echo '<ul>';
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
            <div class="alert alert-success" role="status">
                <i class="fas fa-circle-check" aria-hidden="true"></i>
                <div><?= esc(session()->getFlashdata('success')) ?></div>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('auth/login') ?>" method="POST" novalidate>
            <?= csrf_field() ?>

            <div class="field">
                <label for="username" class="field-label">Username</label>
                <div class="field-control">
                    <i class="fas fa-user lead" aria-hidden="true"></i>
                    <input id="username" name="username" type="text" required
                           placeholder="Masukkan username"
                           value="<?= esc(old('username'), 'attr') ?>"
                           autocomplete="username" autocapitalize="off" autocorrect="off">
                </div>
            </div>

            <div class="field">
                <label for="password" class="field-label">Password</label>
                <div class="field-control">
                    <i class="fas fa-lock lead" aria-hidden="true"></i>
                    <input id="password" name="password" type="password" required
                           class="has-toggle"
                           placeholder="Masukkan password"
                           autocomplete="current-password">
                    <button type="button" class="password-toggle" id="togglePassword"
                            aria-label="Tampilkan password" aria-pressed="false">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                    </button>
                </div>
                <small id="capsHint" class="caps-hint" aria-live="polite">
                    <i class="fas fa-arrow-up" aria-hidden="true"></i> Caps Lock aktif
                </small>
            </div>

            <button type="submit" class="login-submit">
                <span>Masuk</span>
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
            </button>
        </form>
    </section>

    <p class="login-footer">&copy; <?= date('Y') ?> MeetingKU — Sistem Manajemen Meeting</p>
</main>

<script>
(function() {
    'use strict';
    var toggleBtn = document.getElementById('togglePassword');
    var passwordInput = document.getElementById('password');
    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function() {
            var isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleBtn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            toggleBtn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
            var icon = toggleBtn.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
        });
    }

    // Caps lock indicator
    var capsHint = document.getElementById('capsHint');
    function updateCaps(e) {
        if (!capsHint || typeof e.getModifierState !== 'function') return;
        if (e.getModifierState('CapsLock')) {
            capsHint.classList.add('is-on');
        } else {
            capsHint.classList.remove('is-on');
        }
    }
    if (passwordInput) {
        passwordInput.addEventListener('keydown', updateCaps);
        passwordInput.addEventListener('keyup', updateCaps);
        passwordInput.addEventListener('blur', function() {
            capsHint && capsHint.classList.remove('is-on');
        });
    }
})();
</script>
</body>
</html>
