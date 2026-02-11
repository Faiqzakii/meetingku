<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MeetingKU</title>
    <meta name="description" content="Sistem manajemen meeting dan ruangan yang modern dan efisien">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <style>
        /* ===== DESIGN SYSTEM ===== */
        :root {
            --primary-50: #fff7ed;
            --primary-100: #ffedd5;
            --primary-200: #fed7aa;
            --primary-300: #fdba74;
            --primary-400: #fb923c;
            --primary-500: #f97316;
            --primary-600: #ea580c;
            --primary-700: #c2410c;
            --primary-800: #9a3412;
            --primary-900: #7c2d12;
            --violet-500: #f59e0b;
            --violet-600: #d97706;
            --gradient-primary: linear-gradient(135deg, #ea580c 0%, #f97316 50%, #f59e0b 100%);
            --gradient-subtle: linear-gradient(135deg, #fff7ed 0%, #fffbeb 50%, #fefce8 100%);
            --gradient-dark: linear-gradient(135deg, #7c2d12 0%, #9a3412 50%, #78350f 100%);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
            --shadow-lg: 0 10px 25px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
            --shadow-xl: 0 20px 40px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-glow: 0 0 20px rgba(249, 115, 22, 0.15);
            --radius-sm: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.25rem;
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: 250ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== BASE ===== */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--gradient-subtle);
            min-height: 100vh;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none !important; }
        a:hover { text-decoration: none !important; }

        /* ===== ACCENT STRIP ===== */
        .accent-strip {
            height: 4px;
            background: var(--gradient-primary);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }

        /* ===== NAVBAR ===== */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            position: sticky;
            top: 4px;
            z-index: 50;
            transition: box-shadow var(--transition-base);
        }
        .navbar-glass.scrolled {
            box-shadow: var(--shadow-md);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-weight: 800;
            font-size: 1.25rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.025em;
        }
        .nav-brand-icon {
            width: 2.25rem;
            height: 2.25rem;
            background: var(--gradient-primary);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            -webkit-text-fill-color: white;
        }

        .nav-link-custom {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #64748b;
            transition: all var(--transition-base);
            position: relative;
        }
        .nav-link-custom:hover {
            color: var(--primary-700);
            background: var(--primary-50);
        }
        .nav-link-custom.active {
            color: var(--primary-700);
            background: var(--primary-100);
            font-weight: 600;
        }

        .nav-user-avatar {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            background: var(--gradient-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.025em;
        }

        /* ===== MOBILE MENU ===== */
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height var(--transition-slow), opacity var(--transition-base);
            opacity: 0;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }
        .mobile-menu.open {
            max-height: 400px;
            opacity: 1;
        }
        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 500;
            color: #475569;
            transition: all var(--transition-fast);
            border-left: 3px solid transparent;
        }
        .mobile-nav-link:hover,
        .mobile-nav-link.active {
            background: var(--primary-50);
            color: var(--primary-700);
            border-left-color: var(--primary-500);
        }

        /* ===== BUTTONS ===== */
        .btn-primary-gradient {
            background: var(--gradient-primary);
            color: white;
            font-weight: 600;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-sm);
            border: none;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all var(--transition-base);
            box-shadow: 0 2px 8px rgba(234, 88, 12, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary-gradient:hover {
            box-shadow: 0 4px 16px rgba(234, 88, 12, 0.35);
            transform: translateY(-1px);
            color: white;
        }
        .btn-primary-gradient:active {
            transform: translateY(0);
        }

        .btn-outline-custom {
            background: white;
            color: #475569;
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-sm);
            border: 1px solid #e2e8f0;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all var(--transition-base);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-outline-custom:hover {
            border-color: var(--primary-300);
            color: var(--primary-700);
            background: var(--primary-50);
        }

        /* ===== CARDS ===== */
        .card-modern {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all var(--transition-base);
            overflow: hidden;
        }
        .card-modern:hover {
            box-shadow: var(--shadow-md);
        }
        .card-modern-elevated {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(226, 232, 240, 0.5);
            overflow: hidden;
        }

        /* ===== TABLES ===== */
        .table-modern thead th {
            background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #475569;
            padding: 0.875rem 1.25rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .table-modern tbody tr {
            transition: all var(--transition-fast);
        }
        .table-modern tbody tr:hover {
            background: #f8fafc;
        }
        .table-modern tbody td {
            padding: 1rem 1.25rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        /* ===== STATUS BADGES ===== */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .badge-status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-pending::before { background: #f59e0b; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-approved::before { background: #10b981; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-rejected::before { background: #ef4444; }

        /* ===== TOAST NOTIFICATIONS ===== */
        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
        }
        .toast-notification {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-xl);
            font-size: 0.875rem;
            font-weight: 500;
            min-width: 300px;
            animation: toastSlideIn 0.4s cubic-bezier(0.21, 1.02, 0.73, 1) forwards;
            backdrop-filter: blur(10px);
        }
        .toast-notification.toast-hiding {
            animation: toastSlideOut 0.3s ease-in forwards;
        }
        .toast-success {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .toast-error {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .toast-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1px solid #fde68a;
            color: #92400e;
        }
        .toast-icon {
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
        }
        .toast-success .toast-icon { background: #10b981; color: white; }
        .toast-error .toast-icon { background: #ef4444; color: white; }
        .toast-warning .toast-icon { background: #f59e0b; color: white; }
        .toast-close {
            margin-left: auto;
            background: none;
            border: none;
            cursor: pointer;
            color: inherit;
            opacity: 0.5;
            font-size: 1rem;
            padding: 0.25rem;
            transition: opacity var(--transition-fast);
        }
        .toast-close:hover { opacity: 1; }

        @keyframes toastSlideIn {
            from { transform: translateX(100%) scale(0.95); opacity: 0; }
            to { transform: translateX(0) scale(1); opacity: 1; }
        }
        @keyframes toastSlideOut {
            from { transform: translateX(0) scale(1); opacity: 1; }
            to { transform: translateX(100%) scale(0.95); opacity: 0; }
        }

        /* ===== PAGE TRANSITION ===== */
        .page-content {
            animation: fadeInUp 0.4s ease-out;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== SECTION HEADERS ===== */
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: -0.025em;
        }
        .section-title-gradient {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ===== FORM INPUTS ===== */
        .input-modern {
            display: block;
            width: 100%;
            padding: 0.625rem 0.875rem;
            font-size: 0.875rem;
            font-family: 'Inter', sans-serif;
            border: 1.5px solid #e2e8f0;
            border-radius: var(--radius-sm);
            background: white;
            color: #1e293b;
            transition: all var(--transition-base);
        }
        .input-modern:focus {
            outline: none;
            border-color: var(--primary-400);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .input-modern::placeholder {
            color: #94a3b8;
        }

        /* ===== MODALS ===== */
        .modal-content {
            border: none !important;
            border-radius: var(--radius-lg) !important;
            box-shadow: var(--shadow-xl) !important;
            overflow: hidden;
        }
        .modal-header {
            background: linear-gradient(to bottom, #fafaff, #ffffff) !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 1.25rem 1.5rem !important;
        }
        .modal-header .modal-title,
        .modal-header h5 {
            font-weight: 700 !important;
            font-size: 1.125rem !important;
            color: #1e293b !important;
        }
        .modal-footer {
            background: #f8fafc !important;
            border-top: 1px solid #f1f5f9 !important;
            padding: 1rem 1.5rem !important;
        }
        .modal-body {
            padding: 1.5rem !important;
        }
        .modal-backdrop.show {
            opacity: 0.4;
            backdrop-filter: blur(4px);
        }

        /* ===== FULLCALENDAR OVERRIDES ===== */
        .fc {
            font-family: 'Inter', sans-serif !important;
        }
        .fc .fc-toolbar-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #1e293b !important;
        }
        .fc .fc-button {
            background: white !important;
            border: 1.5px solid #e2e8f0 !important;
            color: #475569 !important;
            font-weight: 500 !important;
            font-size: 0.8125rem !important;
            padding: 0.375rem 0.875rem !important;
            border-radius: var(--radius-sm) !important;
            text-transform: capitalize !important;
            box-shadow: none !important;
            transition: all var(--transition-fast) !important;
        }
        .fc .fc-button:hover {
            background: var(--primary-50) !important;
            border-color: var(--primary-300) !important;
            color: var(--primary-700) !important;
        }
        .fc .fc-button-active,
        .fc .fc-button.fc-button-active {
            background: var(--primary-600) !important;
            border-color: var(--primary-600) !important;
            color: white !important;
        }
        .fc .fc-today-button {
            background: var(--primary-50) !important;
            border-color: var(--primary-200) !important;
            color: var(--primary-700) !important;
            font-weight: 600 !important;
        }
        .fc .fc-today-button:hover {
            background: var(--primary-100) !important;
        }
        .fc .fc-daygrid-day.fc-day-today {
            background: rgba(99, 102, 241, 0.06) !important;
        }
        .fc .fc-event {
            border-radius: 6px !important;
            padding: 2px 6px !important;
            font-size: 0.8125rem !important;
            font-weight: 500 !important;
            border: none !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
            cursor: pointer !important;
            transition: transform 0.15s ease, box-shadow 0.15s ease !important;
        }
        .fc .fc-event:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 3px 8px rgba(0,0,0,0.12) !important;
        }
        .fc .fc-col-header-cell {
            padding: 0.625rem 0 !important;
            font-weight: 600 !important;
            font-size: 0.8125rem !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            letter-spacing: 0.03em !important;
        }
        .fc .fc-daygrid-day-number {
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            color: #475569 !important;
            padding: 0.5rem !important;
        }
        .fc th, .fc td {
            border-color: #f1f5f9 !important;
        }
        .fc .fc-scrollgrid {
            border-color: #e2e8f0 !important;
            border-radius: var(--radius-md) !important;
            overflow: hidden !important;
        }

        /* ===== EMPTY STATES ===== */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
        }
        .empty-state-icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: var(--primary-50);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-400);
            font-size: 1.5rem;
        }
        .empty-state-title {
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.375rem;
        }
        .empty-state-desc {
            font-size: 0.875rem;
            color: #94a3b8;
        }

        /* ===== FOOTER ===== */
        .footer {
            background: white;
            border-top: 1px solid #f1f5f9;
            padding: 1.25rem 0;
            margin-top: 3rem;
        }
        .footer-text {
            text-align: center;
            font-size: 0.8125rem;
            color: #94a3b8;
        }
        .footer-brand {
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ===== UTILS ===== */
        .dropdown-modern {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            padding-right: 2.5rem;
        }
    </style>
</head>
<body>
    <!-- Accent strip -->
    <div class="accent-strip"></div>

    <!-- Navbar -->
    <nav class="navbar-glass" id="mainNavbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center" style="height: 4rem;">
                <!-- Left: Brand + Nav links -->
                <div class="flex items-center gap-1">
                    <a href="<?= base_url('/') ?>" class="nav-brand mr-6">
                        <span class="nav-brand-icon">
                            <i class="fas fa-calendar-check"></i>
                        </span>
                        MeetingKU
                    </a>

                    <!-- Desktop nav -->
                    <div class="hidden sm:flex items-center gap-1">
                        <a href="<?= base_url('/') ?>" 
                           class="nav-link-custom <?= current_url() == base_url('/') ? 'active' : '' ?>">
                            <i class="fas fa-calendar-alt text-xs"></i> Calendar
                        </a>
                        <a href="<?= base_url('/upcoming') ?>" 
                           class="nav-link-custom <?= current_url() == base_url('/upcoming') ? 'active' : '' ?>">
                            <i class="fas fa-clock text-xs"></i> Meeting
                        </a>
                        <?php if (session()->get('is_admin')): ?>
                            <a href="<?= base_url('meeting/all') ?>" 
                               class="nav-link-custom <?= current_url() == base_url('meeting/all') ? 'active' : '' ?>">
                                <i class="fas fa-list text-xs"></i> Semua Kegiatan
                            </a>
                            <a href="<?= base_url('pegawai') ?>" 
                               class="nav-link-custom <?= current_url() == base_url('pegawai') ? 'active' : '' ?>">
                                <i class="fas fa-users text-xs"></i> Pegawai
                            </a>
                            <a href="<?= base_url('ruangan') ?>" 
                               class="nav-link-custom <?= current_url() == base_url('ruangan') ? 'active' : '' ?>">
                                <i class="fas fa-door-open text-xs"></i> Ruangan
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: User / Login + Mobile burger -->
                <div class="flex items-center gap-3">
                    <!-- Desktop user area -->
                    <div class="hidden sm:flex items-center gap-3">
                        <?php if (session()->get('logged_in')): ?>
                            <div class="flex items-center gap-3">
                                <div class="nav-user-avatar" title="<?= session()->get('nama') ?>">
                                    <?= strtoupper(substr(session()->get('nama'), 0, 2)) ?>
                                </div>
                                <div class="hidden md:block">
                                    <span class="text-sm font-semibold text-gray-800"><?= session()->get('nama') ?></span>
                                    <?php if (session()->get('is_admin')): ?>
                                        <span class="ml-1 badge-status badge-approved" style="padding:0.125rem 0.5rem; font-size:0.6875rem;">Admin</span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= base_url('auth/logout') ?>" 
                                   class="btn-outline-custom" style="padding:0.375rem 0.875rem; font-size:0.8125rem;">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span class="hidden md:inline">Logout</span>
                                </a>
                            </div>
                        <?php else: ?>
                            <a href="<?= base_url('auth/login') ?>" class="btn-primary-gradient" style="padding:0.5rem 1.125rem; font-size:0.8125rem;">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile burger -->
                    <button id="mobileMenuButton" type="button" 
                            class="sm:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                            aria-controls="mobileMenu" aria-expanded="false">
                        <i class="fas fa-bars text-lg" id="burgerIcon"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile menu -->
    <div id="mobileMenu" class="mobile-menu sm:hidden">
        <div class="py-2">
            <a href="<?= base_url('/') ?>" class="mobile-nav-link <?= current_url() == base_url('/') ? 'active' : '' ?>">
                <i class="fas fa-calendar-alt w-5 text-center"></i> Calendar
            </a>
            <a href="<?= base_url('/upcoming') ?>" class="mobile-nav-link <?= current_url() == base_url('/upcoming') ? 'active' : '' ?>">
                <i class="fas fa-clock w-5 text-center"></i> Meeting
            </a>
            <?php if (session()->get('is_admin')): ?>
                <a href="<?= base_url('meeting/all') ?>" class="mobile-nav-link <?= current_url() == base_url('meeting/all') ? 'active' : '' ?>">
                    <i class="fas fa-list w-5 text-center"></i> Semua Kegiatan
                </a>
                <a href="<?= base_url('pegawai') ?>" class="mobile-nav-link <?= current_url() == base_url('pegawai') ? 'active' : '' ?>">
                    <i class="fas fa-users w-5 text-center"></i> Pegawai
                </a>
                <a href="<?= base_url('ruangan') ?>" class="mobile-nav-link <?= current_url() == base_url('ruangan') ? 'active' : '' ?>">
                    <i class="fas fa-door-open w-5 text-center"></i> Ruangan
                </a>
            <?php endif; ?>
            <div style="border-top: 1px solid #f1f5f9; margin: 0.5rem 0;"></div>
            <?php if (session()->get('logged_in')): ?>
                <div class="px-4 py-2 flex items-center gap-3">
                    <div class="nav-user-avatar" style="width:2rem;height:2rem;font-size:0.6875rem;">
                        <?= strtoupper(substr(session()->get('nama'), 0, 2)) ?>
                    </div>
                    <span class="text-sm font-semibold text-gray-700"><?= session()->get('nama') ?></span>
                </div>
                <a href="<?= base_url('auth/logout') ?>" class="mobile-nav-link" style="color:#ef4444;">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i> Logout
                </a>
            <?php else: ?>
                <a href="<?= base_url('auth/login') ?>" class="mobile-nav-link" style="color:var(--primary-600);">
                    <i class="fas fa-sign-in-alt w-5 text-center"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 page-content" style="min-height: calc(100vh - 180px);">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="footer-text">
                &copy; <?= date('Y') ?> <span class="footer-brand">MeetingKU</span> — Sistem Manajemen Meeting
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function(){
        // Mobile menu toggle
        var btn = document.getElementById('mobileMenuButton');
        var menu = document.getElementById('mobileMenu');
        var icon = document.getElementById('burgerIcon');
        if (btn && menu) {
            btn.addEventListener('click', function(){
                var isOpen = menu.classList.contains('open');
                if (isOpen) {
                    menu.classList.remove('open');
                    icon.classList.replace('fa-times', 'fa-bars');
                    btn.setAttribute('aria-expanded', 'false');
                } else {
                    menu.classList.add('open');
                    icon.classList.replace('fa-bars', 'fa-times');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        }

        // Navbar scroll effect
        var navbar = document.getElementById('mainNavbar');
        if (navbar) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 10) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        }
    })();
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
