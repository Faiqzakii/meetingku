<?php
/**
 * MeetingKU — base layout (Topbar B + warm canvas + orange primary).
 * Single source of truth untuk design tokens.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? esc($pageTitle) . ' — MeetingKU' : 'MeetingKU' ?></title>
    <meta name="description" content="Sistem manajemen meeting dan ruangan yang ringan dan rapi.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" defer></script>

    <style>
        /* ================= DESIGN TOKENS ================= */
        :root {
            --canvas: #fffefb;
            --canvas-soft: #f8f4f0;
            --surface: #ffffff;
            --ink: #0f172a;
            --ink-soft: #1e293b;
            --body: #475569;
            --mute: #94a3b8;
            --border: #e2e8f0;
            --border-strong: #cbd5e1;

            --primary: #ff4f00;
            --primary-soft: #fff1ea;
            --primary-dark: #c2410c;
            --primary-ring: rgba(255, 79, 0, .35);

            --success: #16a34a;
            --success-soft: #dcfce7;
            --warning: #d97706;
            --warning-soft: #fef3c7;
            --danger: #dc2626;
            --danger-soft: #fee2e2;
            --info: #0284c7;
            --info-soft: #e0f2fe;

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-pill: 9999px;

            --shadow-1: 0 1px 2px rgba(15, 23, 42, .04), 0 1px 1px rgba(15, 23, 42, .03);
            --shadow-2: 0 4px 12px rgba(15, 23, 42, .06), 0 2px 4px rgba(15, 23, 42, .04);
            --shadow-3: 0 14px 30px rgba(15, 23, 42, .08), 0 4px 10px rgba(15, 23, 42, .04);
            --shadow-focus: 0 0 0 3px var(--primary-ring);

            --font-display: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
            --font-body: 'Inter', system-ui, -apple-system, sans-serif;

            --topbar-h: 64px;
            --content-max: 1200px;
        }

        /* ================= RESET / BASE ================= */
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        html { background: var(--canvas); }
        body {
            font-family: var(--font-body);
            color: var(--ink);
            background:
                radial-gradient(circle at top right, rgba(255, 79, 0, .05), transparent 30rem),
                linear-gradient(180deg, var(--canvas) 0%, var(--canvas-soft) 100%);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            font-size: 14.5px;
            line-height: 1.55;
        }
        a { color: inherit; text-decoration: none; }
        a:hover { color: var(--primary-dark); }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-display);
            color: var(--ink);
            margin: 0;
            line-height: 1.2;
            letter-spacing: -.01em;
        }
        ::selection { background: var(--primary-soft); color: var(--ink); }
        :focus-visible { outline: none; box-shadow: var(--shadow-focus); border-radius: var(--radius-sm); }

        /* ================= TOPBAR ================= */
        .topbar {
            position: sticky; top: 0; z-index: 50;
            height: var(--topbar-h);
            background: rgba(255, 254, 251, .92);
            backdrop-filter: saturate(180%) blur(14px);
            -webkit-backdrop-filter: saturate(180%) blur(14px);
            border-bottom: 1px solid var(--border);
        }
        .topbar-inner {
            max-width: var(--content-max);
            margin: 0 auto;
            height: 100%;
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 0 20px;
        }
        .brand {
            display: inline-flex; align-items: center; gap: 10px;
            font-family: var(--font-display);
            font-weight: 800; font-size: 1.05rem;
            color: var(--ink);
        }
        .brand-mark {
            width: 32px; height: 32px;
            border-radius: 9px;
            background: var(--primary);
            color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .95rem;
        }
        .nav-list { display: none; gap: 4px; align-items: center; flex-wrap: nowrap; white-space: nowrap; }
        @media (min-width: 960px) { .nav-list { display: inline-flex; } }
        .nav-link {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px;
            border-radius: var(--radius-pill);
            font-size: .875rem; font-weight: 600;
            color: var(--body);
            white-space: nowrap;
            transition: background .15s ease, color .15s ease;
        }
        .nav-link i { font-size: .8em; opacity: .8; }
        .nav-link:hover { background: var(--canvas-soft); color: var(--ink); }
        .nav-link.is-active {
            background: var(--primary-soft); color: var(--primary-dark);
        }

        .topbar-actions { display: inline-flex; align-items: center; gap: 8px; margin-left: auto; }

        .btn { /* base */
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 16px; min-height: 40px;
            font-family: inherit; font-size: .875rem; font-weight: 600;
            border-radius: var(--radius-md); border: 1px solid transparent;
            cursor: pointer; user-select: none;
            transition: background .15s, color .15s, border-color .15s, box-shadow .15s, transform .15s;
            text-decoration: none;
        }
        .btn:disabled, .btn.is-disabled { opacity: .55; cursor: not-allowed; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); color: #fff; }
        .btn-primary:focus-visible { box-shadow: var(--shadow-focus); }
        .btn-secondary { background: var(--surface); color: var(--ink); border-color: var(--border-strong); }
        .btn-secondary:hover { border-color: var(--primary); color: var(--primary-dark); }
        .btn-ghost { background: transparent; color: var(--ink); }
        .btn-ghost:hover { background: var(--canvas-soft); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: #b91c1c; color: #fff; }
        .btn-sm { padding: 6px 12px; min-height: 32px; font-size: .8125rem; }
        .btn-icon { padding: 0; width: 36px; height: 36px; min-height: 36px; }

        /* User menu */
        .user-menu { position: relative; }
        .user-trigger {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 4px 10px 4px 4px;
            border-radius: var(--radius-pill);
            border: 1px solid var(--border);
            background: var(--surface);
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s;
        }
        .user-trigger.user-trigger-icon {
            padding: 0;
            border: 0;
            background: transparent;
            border-radius: 50%;
        }
        .user-trigger:hover { border-color: var(--primary); }
        .user-trigger.user-trigger-icon:hover { border-color: transparent; }
        .user-trigger:focus-visible { box-shadow: var(--shadow-focus); }
        .user-trigger.user-trigger-icon:hover .user-avatar { box-shadow: 0 0 0 2px var(--primary-soft); }
        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: .8rem; font-weight: 800; letter-spacing: .02em;
            transition: box-shadow .15s ease;
        }
        .user-trigger:not(.user-trigger-icon) .user-avatar { width: 32px; height: 32px; font-size: .75rem; }
        .user-name { font-size: .8125rem; font-weight: 600; color: var(--ink); }
        .user-dropdown {
            position: absolute; right: 0; top: calc(100% + 8px);
            min-width: 220px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-3);
            padding: 6px;
            opacity: 0; visibility: hidden; transform: translateY(-4px);
            transition: opacity .15s, transform .15s, visibility .15s;
            z-index: 60;
        }
        .user-menu.is-open .user-dropdown {
            opacity: 1; visibility: visible; transform: translateY(0);
        }
        .user-dropdown-header {
            padding: 10px 12px; border-bottom: 1px solid var(--border); margin-bottom: 6px;
        }
        .user-dropdown-header strong { display: block; font-size: .875rem; color: var(--ink); }
        .user-dropdown-header span { font-size: .75rem; color: var(--mute); }
        .user-dropdown a, .user-dropdown button {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 10px 12px;
            border: 0; background: transparent;
            font: inherit; color: var(--ink);
            border-radius: var(--radius-sm);
            cursor: pointer; text-align: left;
        }
        .user-dropdown a:hover, .user-dropdown button:hover { background: var(--canvas-soft); }
        .user-dropdown .danger { color: var(--danger); }
        .user-dropdown .danger:hover { background: var(--danger-soft); }

        /* Burger / mobile drawer */
        .burger {
            display: inline-flex; align-items: center; justify-content: center;
            width: 40px; height: 40px;
            background: transparent; border: 1px solid var(--border);
            border-radius: var(--radius-md);
            color: var(--ink); cursor: pointer;
        }
        @media (min-width: 960px) { .burger { display: none; } }
        .drawer {
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, .35);
            display: none; z-index: 70;
        }
        .drawer.is-open { display: block; }
        .drawer-panel {
            position: absolute; right: 0; top: 0; bottom: 0;
            width: min(320px, 86vw);
            background: var(--surface);
            display: flex; flex-direction: column;
            border-left: 1px solid var(--border);
            transform: translateX(100%);
            transition: transform .25s ease;
        }
        .drawer.is-open .drawer-panel { transform: translateX(0); }
        .drawer-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; border-bottom: 1px solid var(--border);
        }
        .drawer-nav { padding: 8px; display: flex; flex-direction: column; gap: 2px; }
        .drawer-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            color: var(--ink); font-weight: 600; font-size: .9rem;
        }
        .drawer-link:hover { background: var(--canvas-soft); }
        .drawer-link.is-active { background: var(--primary-soft); color: var(--primary-dark); }
        .drawer-divider { height: 1px; background: var(--border); margin: 8px 4px; }

        /* ================= LAYOUT ================= */
        .page {
            max-width: var(--content-max);
            margin: 0 auto;
            padding: 28px 20px 64px;
        }
        .page-header {
            display: flex; flex-wrap: wrap; align-items: end; justify-content: space-between;
            gap: 16px; margin-bottom: 24px;
        }
        .page-title { font-size: clamp(1.25rem, 1.7vw, 1.5rem); font-weight: 700; line-height: 1.25; letter-spacing: -.01em; }
        .page-subtitle { color: var(--body); margin-top: 4px; font-size: .8125rem; line-height: 1.5; }
        .page-actions { display: inline-flex; gap: 8px; flex-wrap: wrap; align-items: center; }

        /* ================= CARD / SURFACE ================= */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-1);
        }
        .card + .card { margin-top: 16px; }
        .card-section { padding: 20px; }
        .card-divider { height: 1px; background: var(--border); }

        /* Compatibility shims so existing views keep rendering */
        .card-modern, .card-modern-elevated, .stat-card, .stats-card, .content-card {
            background: var(--surface) !important;
            border: 1px solid var(--border) !important;
            border-radius: var(--radius-lg) !important;
            box-shadow: var(--shadow-1) !important;
        }
        .btn-primary-gradient {
            background: var(--primary) !important; color: #fff !important;
            border: 1px solid var(--primary) !important;
            border-radius: var(--radius-md) !important;
            box-shadow: none !important;
            font-weight: 600 !important;
            min-height: 40px;
        }
        .btn-primary-gradient:hover { background: var(--primary-dark) !important; transform: none; }
        .btn-outline-custom {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px;
            line-height: 1;
            white-space: nowrap;
            text-align: center;
            background: var(--surface) !important; color: var(--ink) !important;
            border: 1px solid var(--border-strong) !important;
            border-radius: var(--radius-md) !important;
            box-shadow: none !important;
            font-weight: 600 !important;
            min-height: 40px;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s, color .15s, border-color .15s, box-shadow .15s;
        }
        .btn-outline-custom > i { line-height: 1; }
        .btn-outline-custom:hover { border-color: var(--primary) !important; color: var(--primary-dark) !important; text-decoration: none; }
        .btn-outline-custom:focus-visible { outline: none; box-shadow: var(--shadow-focus) !important; }

        /* ================= FORMS ================= */
        .field { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
        .field-label { font-size: .8125rem; font-weight: 600; color: var(--ink-soft); }
        .input, .form-control, .form-select {
            width: 100%;
            min-height: 42px;
            padding: 10px 12px;
            background: var(--surface);
            color: var(--ink);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-md);
            font: inherit; font-size: .9rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .input:focus, .form-control:focus, .form-select:focus {
            outline: none; border-color: var(--primary); box-shadow: var(--shadow-focus);
        }
        .field-help { font-size: .75rem; color: var(--mute); }
        select.input { appearance: none; -webkit-appearance: none; padding-right: 36px;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
            background-repeat: no-repeat; background-position: right 12px center;
        }

        /* Form grid layout (modal & forms) */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px 16px;
        }
        .form-grid .col-span-2 { grid-column: 1 / -1; }
        @media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } .form-grid .col-span-2 { grid-column: 1 / -1; } }

        /* Checkbox pill grid */
        .check-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .check-grid .check-pill-wide { grid-column: 1 / -1; }
        @media (max-width: 640px) { .check-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .check-pill {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 10px 12px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: .8125rem; font-weight: 500;
            color: var(--ink-soft);
            cursor: pointer;
            transition: border-color .15s, background .15s, box-shadow .15s;
            user-select: none;
        }
        .check-pill:hover { border-color: var(--primary); background: var(--primary-soft); color: var(--primary-dark); }
        .check-pill input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
            flex-shrink: 0;
        }
        .check-pill input[type="checkbox"]:checked + i + span,
        .check-pill input[type="checkbox"]:checked ~ span { color: var(--primary-dark); font-weight: 600; }
        .check-pill:has(input[type="checkbox"]:checked) {
            border-color: var(--primary);
            background: var(--primary-soft);
            color: var(--primary-dark);
        }
        .check-pill i { color: var(--mute); font-size: .85em; flex-shrink: 0; }
        .check-pill:has(input[type="checkbox"]:checked) i { color: var(--primary); }
        .check-pill span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }
        .hidden { display: none !important; }

        /* ================= TABLE ================= */
        .table-modern { width: 100%; border-collapse: separate; border-spacing: 0; table-layout: auto; }
        .table-modern thead th {
            background: var(--canvas-soft);
            color: var(--ink-soft);
            font-size: .7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .04em;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            white-space: nowrap;
        }
        .table-modern tbody td {
            padding: 12px 14px;
            font-size: .85rem;
            color: var(--ink);
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            line-height: 1.45;
        }
        .table-modern tbody td.cell-truncate {
            max-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-modern tbody tr:hover { background: var(--canvas-soft); }
        .table-modern tbody tr:last-child td { border-bottom: 0; }

        /* ================= BADGES ================= */
        .badge-status {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 10px;
            border-radius: var(--radius-pill);
            font-size: .72rem; font-weight: 700;
            letter-spacing: .02em;
            background: var(--canvas-soft); color: var(--ink-soft);
        }
        .badge-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-pending  { background: var(--warning-soft); color: var(--warning); }
        .badge-approved { background: var(--success-soft); color: var(--success); }
        .badge-rejected { background: var(--danger-soft);  color: var(--danger);  }
        .badge-info     { background: var(--info-soft);    color: var(--info);    }
        .badge-primary  { background: var(--primary-soft); color: var(--primary-dark); }

        /* ================= FILTER CHIPS ================= */
        .chip-row { display: inline-flex; flex-wrap: wrap; gap: 6px; }
        .chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px;
            border-radius: var(--radius-pill);
            background: var(--surface);
            border: 1px solid var(--border-strong);
            font-size: .8125rem; font-weight: 600; color: var(--body);
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
        }
        .chip:hover { border-color: var(--primary); color: var(--primary-dark); }
        .chip.is-active {
            background: var(--primary); color: #fff; border-color: var(--primary);
        }

        /* ================= KEBAB MENU ================= */
        .kebab { position: relative; display: inline-block; }
        .kebab-btn {
            width: 32px; height: 32px;
            display: inline-flex; align-items: center; justify-content: center;
            background: transparent; border: 1px solid transparent;
            border-radius: var(--radius-md);
            color: var(--body); cursor: pointer;
        }
        .kebab-btn:hover { background: var(--canvas-soft); border-color: var(--border); }
        .kebab-menu {
            display: none;
            position: absolute; right: 0; top: calc(100% + 4px);
            min-width: 180px;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-md); box-shadow: var(--shadow-2);
            padding: 4px;
            opacity: 0; visibility: hidden; transform: translateY(-4px);
            transition: opacity .15s, transform .15s, visibility .15s;
            z-index: 30;
        }
        .kebab.is-open .kebab-menu { display: block; opacity: 1; visibility: visible; transform: translateY(0); }
        .kebab.is-open .kebab-menu.is-floating {
            position: fixed;
            top: var(--kebab-top);
            left: var(--kebab-left);
            right: auto;
        }
        .kebab-menu a, .kebab-menu button {
            display: flex; align-items: center; gap: 10px;
            width: 100%; padding: 8px 10px;
            border: 0; background: transparent;
            font: inherit; font-size: .85rem; color: var(--ink);
            border-radius: var(--radius-sm); text-align: left;
            cursor: pointer;
        }
        .kebab-menu a:hover, .kebab-menu button:hover { background: var(--canvas-soft); }
        .kebab-menu .danger { color: var(--danger); }
        .kebab-menu .danger:hover { background: var(--danger-soft); }

        /* ================= MODAL ================= */
        .modal-content {
            border: 1px solid var(--border) !important;
            border-radius: var(--radius-lg) !important;
            box-shadow: var(--shadow-3) !important;
            overflow: hidden;
        }
        .modal-header {
            background: var(--surface) !important;
            color: var(--ink) !important;
            border-bottom: 1px solid var(--border) !important;
            padding: 16px 20px !important;
        }
        .modal-header .modal-title { font-family: var(--font-display); font-weight: 700; color: var(--ink) !important; font-size: 1rem; }
        .modal-header .btn-close {
            background: transparent;
            border: 0;
            width: 32px; height: 32px;
            border-radius: var(--radius-sm);
            opacity: .65;
            transition: opacity .15s, background .15s;
        }
        .modal-header .btn-close:hover { opacity: 1; background: var(--canvas-soft); }
        .modal-footer {
            background: var(--surface) !important;
            border-top: 1px solid var(--border) !important;
            padding: 14px 20px !important;
            gap: 8px !important;
            justify-content: flex-end !important;
        }
        .modal-footer > * { margin: 0 !important; }
        .modal-body { padding: 20px !important; }

        /* ================= TOASTS ================= */
        .toast-container { position: fixed; bottom: 1.25rem; right: 1.25rem; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
        .toast-notification {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 14px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-left: 4px solid var(--primary);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-3);
            font-size: .875rem; color: var(--ink);
            min-width: 280px;
            animation: toastIn .3s cubic-bezier(.21,1.02,.73,1) both;
        }
        .toast-notification.toast-success { border-left-color: var(--success); }
        .toast-notification.toast-error { border-left-color: var(--danger); }
        .toast-notification.toast-warning { border-left-color: var(--warning); }
        .toast-notification.toast-hiding { animation: toastOut .25s ease-in forwards; }
        @keyframes toastIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes toastOut { to { opacity: 0; transform: translateY(8px); } }

        /* ================= CENTER ALERT (form validation) ================= */
        .center-alert-backdrop {
            position: fixed; inset: 0; z-index: 10000;
            background: rgba(15, 23, 42, .55);
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
            animation: centerAlertFadeIn .15s ease-out;
        }
        .center-alert-backdrop.is-hiding { animation: centerAlertFadeOut .15s ease-in forwards; }
        .center-alert {
            background: var(--surface);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-3);
            border: 1px solid var(--border);
            border-top: 4px solid var(--warning);
            max-width: 440px; width: 100%;
            padding: 22px 24px 18px;
            animation: centerAlertPop .18s cubic-bezier(.21,1.02,.73,1);
        }
        .center-alert.is-error { border-top-color: var(--danger); }
        .center-alert-title {
            display: flex; align-items: center; gap: 10px;
            font-size: 1rem; font-weight: 700; color: var(--ink);
            margin: 0 0 8px;
        }
        .center-alert.is-error .center-alert-title i { color: var(--danger); }
        .center-alert-title i { color: var(--warning); font-size: 1.15rem; }
        .center-alert-body {
            color: var(--body); font-size: .875rem; line-height: 1.5;
            margin: 0 0 16px;
        }
        .center-alert-list {
            margin: 6px 0 0; padding-left: 18px;
            color: var(--body); font-size: .85rem; line-height: 1.55;
        }
        .center-alert-list li { margin-bottom: 2px; }
        .center-alert-actions {
            display: flex; justify-content: flex-end; gap: 8px;
        }
        @keyframes centerAlertFadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes centerAlertFadeOut { to { opacity: 0; } }
        @keyframes centerAlertPop {
            from { opacity: 0; transform: translateY(-6px) scale(.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ================= FULLCALENDAR THEMING ================= */
        .fc { font-family: var(--font-body); font-size: .875rem; }
        .fc .fc-toolbar.fc-header-toolbar { margin-bottom: 1.25em; gap: 8px; flex-wrap: wrap; }
        .fc .fc-toolbar-title {
            font-family: var(--font-display);
            font-weight: 700;
            color: var(--ink);
            font-size: clamp(1.05rem, 1.6vw, 1.25rem) !important;
            letter-spacing: -.01em;
        }
        .fc .fc-button-group { gap: 0; }
        .fc .fc-button-primary {
            background: var(--surface) !important;
            color: var(--body) !important;
            border: 1px solid var(--border) !important;
            box-shadow: none !important;
            border-radius: var(--radius-sm) !important;
            font-weight: 500 !important;
            font-size: .8125rem !important;
            padding: 6px 12px !important;
            min-height: 34px;
            line-height: 1 !important;
            text-transform: capitalize !important;
            transition: background .15s, color .15s, border-color .15s;
        }
        .fc .fc-button-primary:hover {
            background: var(--canvas-soft) !important;
            border-color: var(--border-strong) !important;
            color: var(--ink) !important;
        }
        .fc .fc-button-primary:focus-visible { box-shadow: var(--shadow-focus) !important; }
        /* segmented view switcher */
        .fc .fc-button-group > .fc-button-primary { border-radius: 0 !important; }
        .fc .fc-button-group > .fc-button-primary:first-child { border-top-left-radius: var(--radius-sm) !important; border-bottom-left-radius: var(--radius-sm) !important; }
        .fc .fc-button-group > .fc-button-primary:last-child { border-top-right-radius: var(--radius-sm) !important; border-bottom-right-radius: var(--radius-sm) !important; }
        .fc .fc-button-group > .fc-button-primary + .fc-button-primary { margin-left: -1px; }
        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: var(--canvas-soft) !important;
            color: var(--ink) !important;
            border-color: var(--border-strong) !important;
            font-weight: 600 !important;
            position: relative;
            z-index: 1;
        }
        /* Today button: subtle, not loud */
        .fc .fc-today-button {
            background: var(--surface) !important;
            color: var(--ink) !important;
            border-color: var(--border-strong) !important;
            font-weight: 600 !important;
        }
        .fc .fc-today-button:hover { background: var(--canvas-soft) !important; }
        .fc .fc-today-button:disabled { opacity: .55; }
        /* prev/next as icon-style ghost */
        .fc .fc-prev-button, .fc .fc-next-button {
            width: 34px; padding: 6px 0 !important;
            color: var(--ink) !important;
        }

        .fc .fc-daygrid-day.fc-day-today { background: var(--primary-soft) !important; }
        .fc .fc-col-header-cell-cushion {
            font-size: .75rem; font-weight: 600;
            color: var(--mute);
            text-transform: uppercase; letter-spacing: .04em;
            padding: 8px 4px;
        }
        .fc .fc-daygrid-day-number {
            color: var(--body); font-size: .8rem; font-weight: 600;
            padding: 6px 8px;
        }
        .fc .fc-day-today .fc-daygrid-day-number { color: var(--primary-dark); font-weight: 800; }

        /* Status-aware events (legend match) */
        .fc .fc-event,
        .fc .fc-event .fc-event-main,
        .fc .fc-event .fc-event-main-frame,
        .fc .fc-event .fc-event-title,
        .fc .fc-event .fc-event-time,
        .fc .fc-event .fc-event-title-container {
            color: inherit !important;
        }
        .fc .fc-event {
            border: 0 !important;
            border-left: 3px solid transparent !important;
            border-radius: var(--radius-sm) !important;
            padding: 2px 6px !important;
            font-size: .75rem !important;
            font-weight: 600 !important;
            cursor: pointer;
            overflow: hidden;
        }
        .fc .fc-event .fc-event-title,
        .fc .fc-event .fc-event-title-container {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        /* Week/Day view: allow text wrap inside event blocks */
        .fc-timegrid-event,
        .fc-timegrid-event .fc-event-main,
        .fc-timegrid-event .fc-event-main-frame {
            white-space: normal !important;
            overflow: hidden;
        }
        .fc-timegrid-event .fc-event-title,
        .fc-timegrid-event .fc-event-title-container {
            white-space: normal !important;
            overflow: hidden;
            text-overflow: clip;
            word-break: break-word;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 4;
            line-clamp: 4;
        }
        .fc-timegrid-event .fc-event-time {
            display: block;
            margin-bottom: 2px;
            opacity: .85;
        }
        .fc .fc-event-status-approved,
        .fc .fc-event-status-approved .fc-event-main,
        .fc .fc-event-status-approved .fc-event-main-frame,
        .fc .fc-event-status-approved .fc-event-title,
        .fc .fc-event-status-approved .fc-event-time {
            background: var(--success-soft) !important;
            color: #14532d !important;
            border-left-color: var(--success) !important;
        }
        .fc .fc-event-status-pending,
        .fc .fc-event-status-pending .fc-event-main,
        .fc .fc-event-status-pending .fc-event-main-frame,
        .fc .fc-event-status-pending .fc-event-title,
        .fc .fc-event-status-pending .fc-event-time {
            background: var(--warning-soft) !important;
            color: #78350f !important;
            border-left-color: var(--warning) !important;
        }
        .fc .fc-event-status-rejected,
        .fc .fc-event-status-rejected .fc-event-main,
        .fc .fc-event-status-rejected .fc-event-main-frame,
        .fc .fc-event-status-rejected .fc-event-title,
        .fc .fc-event-status-rejected .fc-event-time {
            background: var(--danger-soft) !important;
            color: #7f1d1d !important;
            border-left-color: var(--danger) !important;
        }
        /* TimeGrid (week/day) keep colored fill but readable dark text */
        .fc-timegrid-event,
        .fc-timegrid-event .fc-event-main {
            color: inherit !important;
        }
        .fc-timegrid-event.fc-event-status-approved { background: var(--success-soft) !important; }
        .fc-timegrid-event.fc-event-status-pending  { background: var(--warning-soft) !important; }
        .fc-timegrid-event.fc-event-status-rejected { background: var(--danger-soft) !important; }
        .fc .fc-event:hover { filter: brightness(.97); }
        .fc .fc-event-time {
            font-weight: 700; opacity: 1; margin-right: 4px;
        }

        /* ================= UTILITIES ================= */
        .stack-sm > * + * { margin-top: 8px; }
        .stack > * + * { margin-top: 16px; }
        .row-actions { display: inline-flex; gap: 6px; align-items: center; }

        /* Text truncation */
        .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 100%; }
        .clamp-1, .clamp-2, .clamp-3 {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }
        .clamp-1 { -webkit-line-clamp: 1; line-clamp: 1; }
        .clamp-2 { -webkit-line-clamp: 2; line-clamp: 2; }
        .clamp-3 { -webkit-line-clamp: 3; line-clamp: 3; }

        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: var(--body);
        }
        .empty-state i { font-size: 1.6rem; color: var(--mute); margin-bottom: 8px; }
        .footer {
            border-top: 1px solid var(--border);
            background: var(--canvas);
            padding: 24px 0;
            margin-top: 40px;
        }
        .footer-text { text-align: center; color: var(--mute); font-size: .8125rem; }
        .footer-brand { color: var(--primary-dark); font-weight: 700; }

        /* ================= LEGACY SHIMS (kompatibilitas markup view lama) ================= */
        .section-title {
            font-family: var(--font-display);
            font-size: clamp(1.25rem, 1.7vw, 1.5rem);
            font-weight: 700;
            color: var(--ink);
            margin: 0 0 4px;
            letter-spacing: -.01em;
            line-height: 1.25;
            display: inline-flex; align-items: center; gap: 10px;
        }
        .section-title i { color: var(--primary); font-size: .8em; opacity: .9; }
        .page-title { font-size: clamp(1.25rem, 1.7vw, 1.5rem); font-weight: 700; }
        .input-modern, .dropdown-modern {
            min-height: 42px;
            padding: 10px 12px;
            background: var(--surface);
            color: var(--ink);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-md);
            font: inherit; font-size: .9rem;
            transition: border-color .15s, box-shadow .15s;
        }
        .input-modern:focus, .dropdown-modern:focus {
            outline: none; border-color: var(--primary); box-shadow: var(--shadow-focus);
        }
        .empty-state-icon {
            width: 48px; height: 48px;
            margin: 0 auto 10px;
            background: var(--canvas-soft);
            border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            color: var(--mute);
        }
        .empty-state-title { font-weight: 700; color: var(--ink); margin: 0 0 4px; font-size: .95rem; }
        .empty-state-desc { color: var(--body); font-size: .85rem; margin: 0; }
        .toast-icon {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--canvas-soft);
            color: var(--ink);
        }
        .toast-success .toast-icon { background: var(--success-soft); color: var(--success); }
        .toast-error .toast-icon { background: var(--danger-soft); color: var(--danger); }
        .toast-warning .toast-icon { background: var(--warning-soft); color: var(--warning); }
        .toast-close {
            background: transparent; border: 0; color: var(--mute); cursor: pointer;
            margin-left: auto; padding: 4px; border-radius: 6px;
        }
        .toast-close:hover { background: var(--canvas-soft); color: var(--ink); }
        .input-label, label {
            font-size: .8125rem; font-weight: 600; color: var(--ink-soft);
            text-transform: none; letter-spacing: 0;
        }
        /* Detail row reveal */
        .details-row.hidden { display: none; }
        .details-row td {
            background: var(--canvas-soft) !important;
            border-bottom: 1px solid var(--border) !important;
        }

        /* Reduce motion respect */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>

<?php
    $loggedIn = (bool) session()->get('logged_in');
    $isAdmin  = (bool) session()->get('is_admin');
    $userName = session()->get('nama') ?: 'Tamu';
    $base     = base_url();
    $current  = current_url();
    $is = static fn(string $p) => $current === base_url($p);
?>

<header class="topbar" id="topbar">
    <div class="topbar-inner">
        <a href="<?= $base ?>" class="brand" aria-label="MeetingKU home">
            <span class="brand-mark"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
            <span>MeetingKU</span>
        </a>

        <?php if ($loggedIn): ?>
        <nav class="nav-list" aria-label="Navigasi utama">
            <a href="<?= base_url('/') ?>" class="nav-link <?= $is('/') ? 'is-active' : '' ?>">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i> Calendar
            </a>
            <a href="<?= base_url('/upcoming') ?>" class="nav-link <?= $is('/upcoming') ? 'is-active' : '' ?>">
                <i class="fas fa-clock" aria-hidden="true"></i> Meeting
            </a>
            <?php if ($isAdmin): ?>
                <a href="<?= base_url('meeting/all') ?>" class="nav-link <?= $is('meeting/all') ? 'is-active' : '' ?>">
                    <i class="fas fa-list" aria-hidden="true"></i> Semua Kegiatan
                </a>
                <a href="<?= base_url('pegawai') ?>" class="nav-link <?= $is('pegawai') ? 'is-active' : '' ?>">
                    <i class="fas fa-users" aria-hidden="true"></i> Pegawai
                </a>
                <a href="<?= base_url('ruangan') ?>" class="nav-link <?= $is('ruangan') ? 'is-active' : '' ?>">
                    <i class="fas fa-door-open" aria-hidden="true"></i> Ruangan
                </a>
                <a href="<?= base_url('whatsapp') ?>" class="nav-link <?= $is('whatsapp') ? 'is-active' : '' ?>">
                    <i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp
                </a>
                <a href="<?= base_url('api-keys') ?>" class="nav-link <?= $is('api-keys') ? 'is-active' : '' ?>">
                    <i class="fas fa-key" aria-hidden="true"></i> API Keys
                </a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <div class="topbar-actions">
            <?php if ($loggedIn): ?>
                <a href="<?= base_url('/') ?>?action=create" class="btn btn-primary" id="ctaCreateMeeting"
                   data-bs-toggle="modal" data-bs-target="#createMeetingModal">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    <span>Meeting</span>
                </a>

                <div class="user-menu" id="userMenu">
                    <button type="button" class="user-trigger user-trigger-icon" aria-haspopup="menu" aria-expanded="false" id="userTrigger" aria-label="Menu pengguna <?= esc($userName, 'attr') ?>">
                        <span class="user-avatar"><?= esc(strtoupper(mb_substr($userName, 0, 2))) ?></span>
                    </button>
                    <div class="user-dropdown" role="menu" aria-labelledby="userTrigger">
                        <div class="user-dropdown-header">
                            <strong><?= esc($userName) ?></strong>
                            <span><?= $isAdmin ? 'Admin' : 'Pengguna' ?></span>
                        </div>
                        <a href="<?= base_url('/upcoming') ?>" role="menuitem">
                            <i class="fas fa-clock" aria-hidden="true"></i> Meeting saya
                        </a>
                        <?php if ($isAdmin): ?>
                            <a href="<?= base_url('pegawai') ?>" role="menuitem">
                                <i class="fas fa-users-cog" aria-hidden="true"></i> Kelola pegawai
                            </a>
                            <a href="<?= base_url('whatsapp') ?>" role="menuitem">
                                <i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp Gateway
                            </a>
                            <a href="<?= base_url('api-keys') ?>" role="menuitem">
                                <i class="fas fa-key" aria-hidden="true"></i> API Keys
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url('auth/logout') ?>" role="menuitem" class="danger">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout
                        </a>
                    </div>
                </div>

                <button type="button" class="burger" id="burger" aria-controls="drawer" aria-expanded="false" aria-label="Buka menu">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
            <?php else: ?>
                <a href="<?= base_url('auth/login') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($loggedIn): ?>
<div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-panel" role="dialog" aria-modal="true" aria-label="Menu navigasi">
        <div class="drawer-header">
            <strong style="font-family:var(--font-display);">Menu</strong>
            <button type="button" class="btn btn-icon btn-ghost" id="drawerClose" aria-label="Tutup menu">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
        <nav class="drawer-nav" aria-label="Navigasi mobile">
            <a href="<?= base_url('/') ?>" class="drawer-link <?= $is('/') ? 'is-active' : '' ?>">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i> Calendar
            </a>
            <a href="<?= base_url('/upcoming') ?>" class="drawer-link <?= $is('/upcoming') ? 'is-active' : '' ?>">
                <i class="fas fa-clock" aria-hidden="true"></i> Meeting
            </a>
            <?php if ($isAdmin): ?>
                <a href="<?= base_url('meeting/all') ?>" class="drawer-link <?= $is('meeting/all') ? 'is-active' : '' ?>">
                    <i class="fas fa-list" aria-hidden="true"></i> Semua Kegiatan
                </a>
                <a href="<?= base_url('pegawai') ?>" class="drawer-link <?= $is('pegawai') ? 'is-active' : '' ?>">
                    <i class="fas fa-users" aria-hidden="true"></i> Pegawai
                </a>
                <a href="<?= base_url('ruangan') ?>" class="drawer-link <?= $is('ruangan') ? 'is-active' : '' ?>">
                    <i class="fas fa-door-open" aria-hidden="true"></i> Ruangan
                </a>
                <a href="<?= base_url('whatsapp') ?>" class="drawer-link <?= $is('whatsapp') ? 'is-active' : '' ?>">
                    <i class="fab fa-whatsapp" aria-hidden="true"></i> WhatsApp
                </a>
                <a href="<?= base_url('api-keys') ?>" class="drawer-link <?= $is('api-keys') ? 'is-active' : '' ?>">
                    <i class="fas fa-key" aria-hidden="true"></i> API Keys
                </a>
            <?php endif; ?>
        </nav>
    </div>
</div>
<?php endif; ?>

<main class="page" id="main">
    <?= $this->renderSection('content') ?>
</main>

<footer class="footer">
    <p class="footer-text">
        &copy; <?= date('Y') ?> <span class="footer-brand">MeetingKU</span> — Sistem Manajemen Meeting
    </p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script>
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // ===== User dropdown =====
        var userMenu = document.getElementById('userMenu');
        var userTrigger = document.getElementById('userTrigger');
        if (userMenu && userTrigger) {
            userTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                var open = userMenu.classList.toggle('is-open');
                userTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            document.addEventListener('click', function(e) {
                if (!userMenu.contains(e.target)) {
                    userMenu.classList.remove('is-open');
                    userTrigger.setAttribute('aria-expanded', 'false');
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    userMenu.classList.remove('is-open');
                    userTrigger.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // ===== Mobile drawer =====
        var burger = document.getElementById('burger');
        var drawer = document.getElementById('drawer');
        var drawerClose = document.getElementById('drawerClose');
        function openDrawer() {
            if (!drawer) return;
            drawer.classList.add('is-open');
            drawer.setAttribute('aria-hidden', 'false');
            burger && burger.setAttribute('aria-expanded', 'true');
        }
        function closeDrawer() {
            if (!drawer) return;
            drawer.classList.remove('is-open');
            drawer.setAttribute('aria-hidden', 'true');
            burger && burger.setAttribute('aria-expanded', 'false');
        }
        burger && burger.addEventListener('click', openDrawer);
        drawerClose && drawerClose.addEventListener('click', closeDrawer);
        drawer && drawer.addEventListener('click', function(e) {
            if (e.target === drawer) closeDrawer();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeDrawer();
        });

        // ===== Generic kebab menus =====
        function closeKebab(kebab) {
            kebab.classList.remove('is-open');
            var b = kebab.querySelector('.kebab-btn');
            var menu = kebab.querySelector('.kebab-menu');
            b && b.setAttribute('aria-expanded', 'false');
            if (menu) {
                menu.classList.remove('is-floating');
                menu.style.removeProperty('--kebab-top');
                menu.style.removeProperty('--kebab-left');
            }
        }

        function positionKebab(kebab) {
            var btn = kebab.querySelector('.kebab-btn');
            var menu = kebab.querySelector('.kebab-menu');
            if (!btn || !menu) return;

            menu.classList.add('is-floating');
            var btnRect = btn.getBoundingClientRect();
            var menuRect = menu.getBoundingClientRect();
            var gap = 6;
            var viewportGap = 8;
            var left = btnRect.right - menuRect.width;
            var top = btnRect.bottom + gap;

            left = Math.max(viewportGap, Math.min(left, window.innerWidth - menuRect.width - viewportGap));
            if (top + menuRect.height > window.innerHeight - viewportGap) {
                top = Math.max(viewportGap, btnRect.top - menuRect.height - gap);
            }

            menu.style.setProperty('--kebab-left', left + 'px');
            menu.style.setProperty('--kebab-top', top + 'px');
        }

        document.querySelectorAll('[data-kebab]').forEach(function(kebab) {
            var btn = kebab.querySelector('.kebab-btn');
            if (!btn) return;
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var isOpen = kebab.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                document.querySelectorAll('[data-kebab].is-open').forEach(function(other) {
                    if (other !== kebab) {
                        closeKebab(other);
                    }
                });
                if (isOpen) positionKebab(kebab); else closeKebab(kebab);
            });
        });
        document.addEventListener('click', function() {
            document.querySelectorAll('[data-kebab].is-open').forEach(function(k) {
                closeKebab(k);
            });
        });
        window.addEventListener('resize', function() {
            document.querySelectorAll('[data-kebab].is-open').forEach(positionKebab);
        });
        window.addEventListener('scroll', function() {
            document.querySelectorAll('[data-kebab].is-open').forEach(positionKebab);
        }, true);

        // ===== CTA fallback (if modal target absent on this page, jump to All) =====
        var cta = document.getElementById('ctaCreateMeeting');
        if (cta) {
            cta.addEventListener('click', function(e) {
                var modalEl = document.getElementById('createMeetingModal');
                if (!modalEl) {
                    e.preventDefault();
                    window.location.href = '<?= base_url('/') ?>?action=create';
                }
            });
        }
    });

    // ===== Toast helper (global) =====
    window.showToast = function(message, type) {
        type = type || 'success';
        var container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        var icon = type === 'success' ? 'check-circle'
                 : type === 'error'   ? 'times-circle'
                 : type === 'warning' ? 'exclamation-circle'
                 : 'info-circle';
        var el = document.createElement('div');
        el.className = 'toast-notification toast-' + type;
        el.setAttribute('role', 'status');
        el.innerHTML = '<i class="fas fa-' + icon + '" aria-hidden="true"></i><span>' + message + '</span>';
        container.appendChild(el);
        setTimeout(function() {
            el.classList.add('toast-hiding');
            setTimeout(function() { el.remove(); }, 250);
        }, 3500);
    };

    // ===== Center alert helper (global) =====
    // Usage: showCenterAlert({ title, body, items, type, confirmText })
    // Returns the backdrop element (so caller can dismiss programmatically).
    window.showCenterAlert = function(opts) {
        opts = opts || {};
        var type = opts.type === 'error' ? 'error' : 'warning';
        var title = opts.title || (type === 'error' ? 'Terjadi kesalahan' : 'Periksa kembali isian');
        var body = opts.body || '';
        var items = Array.isArray(opts.items) ? opts.items : null;
        var confirmText = opts.confirmText || 'Mengerti';

        var existing = document.querySelector('.center-alert-backdrop');
        if (existing) existing.remove();

        var backdrop = document.createElement('div');
        backdrop.className = 'center-alert-backdrop';
        backdrop.setAttribute('role', 'alertdialog');
        backdrop.setAttribute('aria-modal', 'true');
        backdrop.setAttribute('aria-labelledby', 'centerAlertTitle');

        var alert = document.createElement('div');
        alert.className = 'center-alert' + (type === 'error' ? ' is-error' : '');

        var icon = type === 'error' ? 'circle-exclamation' : 'triangle-exclamation';
        var listHtml = '';
        if (items && items.length) {
            listHtml = '<ul class="center-alert-list">';
            for (var i = 0; i < items.length; i++) {
                var li = document.createElement('li');
                li.textContent = items[i];
                listHtml += li.outerHTML;
            }
            listHtml += '</ul>';
        }

        // body is plain text; escape it via textContent then read innerHTML
        var bodyEl = document.createElement('p');
        bodyEl.className = 'center-alert-body';
        bodyEl.textContent = body;

        var titleEl = document.createElement('h5');
        titleEl.className = 'center-alert-title';
        titleEl.id = 'centerAlertTitle';
        titleEl.innerHTML = '<i class="fas fa-' + icon + '" aria-hidden="true"></i><span></span>';
        titleEl.querySelector('span').textContent = title;

        alert.appendChild(titleEl);
        if (body) alert.appendChild(bodyEl);
        if (listHtml) {
            var listWrap = document.createElement('div');
            listWrap.innerHTML = listHtml;
            alert.appendChild(listWrap.firstChild);
        }

        var actions = document.createElement('div');
        actions.className = 'center-alert-actions';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-primary';
        btn.textContent = confirmText;
        actions.appendChild(btn);
        alert.appendChild(actions);
        backdrop.appendChild(alert);
        document.body.appendChild(backdrop);

        function dismiss() {
            backdrop.classList.add('is-hiding');
            setTimeout(function() { backdrop.remove(); }, 150);
            document.removeEventListener('keydown', onKey);
        }
        function onKey(e) {
            if (e.key === 'Escape' || e.key === 'Enter') { e.preventDefault(); dismiss(); }
        }
        btn.addEventListener('click', dismiss);
        backdrop.addEventListener('click', function(e) { if (e.target === backdrop) dismiss(); });
        document.addEventListener('keydown', onKey);
        setTimeout(function() { btn.focus(); }, 50);
        return backdrop;
    };

    // ===== Form guard: client-side rule enforcement (global) =====
    // Inputs declare rules via data-* attributes; mirror server-side rules so
    // request never reaches server when user can fix the issue locally.
    //   data-rule-label="Nama kegiatan"   (label dipakai di pesan)
    //   data-rule-required="1"
    //   data-rule-min="3"                  (min length / min value)
    //   data-rule-max="100"                (max length / max value)
    //   data-rule-pattern="^\\d{18}$"      (regex)
    //   data-rule-pattern-message="..."    (custom message bila pattern gagal)
    //   data-rule-type="number"            (treat min/max as numeric)
    // Forms harus punya class .js-validated agar guard aktif otomatis.
    (function setupFormGuard() {
        function fieldLabel(input) {
            var lbl = input.getAttribute('data-rule-label');
            if (lbl) return lbl;
            if (input.id) {
                var byFor = document.querySelector('label[for="' + input.id + '"]');
                if (byFor) return byFor.textContent.trim();
            }
            return input.getAttribute('name') || 'Input';
        }

        function validateInput(input) {
            // Skip elemen yang tersembunyi atau dinonaktifkan: tidak akan dikirim.
            if (input.disabled) return null;
            // hidden via display:none container? Cek offsetParent kecuali type=hidden yang sah.
            if (input.type !== 'hidden' && input.offsetParent === null && !input.required) return null;

            var label = fieldLabel(input);
            var raw = input.value == null ? '' : String(input.value);
            var value = raw.trim();
            var ruleType = input.getAttribute('data-rule-type') || '';
            var isRequired = input.hasAttribute('required') || input.getAttribute('data-rule-required') === '1';

            if (isRequired && value === '') {
                return label + ' wajib diisi.';
            }
            if (value === '') return null; // optional & empty

            var min = input.getAttribute('data-rule-min');
            var max = input.getAttribute('data-rule-max');

            if (ruleType === 'number') {
                var num = Number(value);
                if (!Number.isFinite(num)) return label + ' harus berupa angka.';
                if (min !== null && num < Number(min)) return label + ' minimal ' + min + '.';
                if (max !== null && num > Number(max)) return label + ' maksimal ' + max + '.';
            } else {
                if (min !== null && value.length < Number(min)) {
                    return label + ' minimal ' + min + ' karakter (saat ini ' + value.length + ').';
                }
                if (max !== null && value.length > Number(max)) {
                    return label + ' maksimal ' + max + ' karakter (saat ini ' + value.length + ').';
                }
            }

            var pattern = input.getAttribute('data-rule-pattern');
            if (pattern) {
                try {
                    var re = new RegExp(pattern);
                    if (!re.test(value)) {
                        return input.getAttribute('data-rule-pattern-message') || (label + ' format tidak valid.');
                    }
                } catch (_) { /* ignore bad regex */ }
            }
            return null;
        }

        function collectErrors(form) {
            var errors = [];
            var firstInvalid = null;
            var inputs = form.querySelectorAll('input, select, textarea');
            for (var i = 0; i < inputs.length; i++) {
                var input = inputs[i];
                // Skip elemen yang berada di container .hidden / display:none.
                var hidden = input.closest('.hidden');
                if (hidden && input.type !== 'hidden') continue;
                var msg = validateInput(input);
                if (msg) {
                    errors.push(msg);
                    if (!firstInvalid) firstInvalid = input;
                }
            }
            return { errors: errors, firstInvalid: firstInvalid };
        }

        function reEnableSubmit(form) {
            // Beberapa form punya tombol yang langsung di-disable di handler submit
            // setelah event listener kita jalan. Pastikan tidak terkunci ketika invalid.
            form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function(b) {
                b.disabled = false;
            });
        }

        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !(form instanceof HTMLFormElement)) return;
            if (!form.classList.contains('js-validated')) return;

            var result = collectErrors(form);
            if (result.errors.length === 0) return;

            e.preventDefault();
            e.stopPropagation();
            reEnableSubmit(form);
            window.showCenterAlert({
                title: 'Periksa kembali isian',
                body: 'Beberapa data belum sesuai aturan. Perbaiki dulu sebelum disimpan:',
                items: result.errors,
                type: 'warning'
            });
            if (result.firstInvalid && typeof result.firstInvalid.focus === 'function') {
                try { result.firstInvalid.focus({ preventScroll: false }); } catch (_) { result.firstInvalid.focus(); }
            }
        }, true); // capture phase: jalan sebelum handler form yang men-disable submit
    })();
})();
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
