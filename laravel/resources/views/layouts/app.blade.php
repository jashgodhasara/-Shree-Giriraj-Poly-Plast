<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shree Giriraj Poly Plast')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --accent: #f59e0b;
            --accent2: #10b981;
            --sidebar-bg: #0f172a;
            --sidebar-border: rgba(255,255,255,0.06);
            --bg: #f0f2f8;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
            --shadow: 0 4px 16px rgba(0,0,0,.08);
            --shadow-lg: 0 12px 40px rgba(0,0,0,.12);
            --radius: 12px;
            --radius-sm: 8px;
        }
        html { scroll-behavior: smooth; width: 100%; max-width: 100vw; overflow-x: hidden; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; width: 100%; max-width: 100vw; overflow-x: hidden; -webkit-text-size-adjust: 100%; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 260px; background: var(--sidebar-bg);
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; height: 100vh;
            overflow-y: auto; z-index: 200;
            border-right: 1px solid var(--sidebar-border);
            transition: transform .3s ease;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }

        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--sidebar-border);
            background: linear-gradient(135deg, rgba(99,102,241,.15), rgba(16,185,129,.08));
        }
        .sidebar-logo {
            display: flex; align-items: center; gap: 12px; margin-bottom: 6px;
        }
        .sidebar-logo-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff;
            box-shadow: 0 4px 14px rgba(99,102,241,.4);
            flex-shrink: 0;
        }
        .sidebar-logo-text h2 {
            font-size: 13px; font-weight: 800; color: #fff;
            letter-spacing: .3px; line-height: 1.3;
            text-transform: uppercase;
        }
        .sidebar-logo-text small { font-size: 10px; color: #94a3b8; font-weight: 400; }
        .sidebar-tagline {
            font-size: 10px; color: #475569;
            padding: 6px 10px; background: rgba(255,255,255,.04);
            border-radius: 6px; margin-top: 4px;
            display: flex; align-items: center; gap: 6px;
        }
        .sidebar-tagline::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: #10b981; flex-shrink: 0;
            box-shadow: 0 0 6px #10b981;
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot { 0%,100%{opacity:1} 50%{opacity:.3} }

        .sidebar-section {
            padding: 16px 20px 6px;
            font-size: 9.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1.2px;
            color: #334155;
        }
        .sidebar-section:first-of-type { margin-top: 8px; }

        .sidebar a {
            display: flex; align-items: center; gap: 11px;
            padding: 9px 20px; color: #94a3b8;
            text-decoration: none; font-size: 13px; font-weight: 500;
            transition: all .2s; position: relative;
            border-radius: 0;
        }
        .sidebar a .nav-icon {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; flex-shrink: 0;
            background: rgba(255,255,255,.04);
            transition: all .2s;
        }
        .sidebar a:hover { color: #e2e8f0; }
        .sidebar a:hover .nav-icon { background: rgba(99,102,241,.2); color: #818cf8; }
        .sidebar a.active { color: #fff; }
        .sidebar a.active .nav-icon {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 10px rgba(99,102,241,.35);
        }
        .sidebar a.active::after {
            content: ''; position: absolute; right: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 24px; border-radius: 3px 0 0 3px;
            background: linear-gradient(#6366f1, #8b5cf6);
        }
        .nav-label { flex: 1; }
        .nav-badge {
            font-size: 10px; font-weight: 700; padding: 2px 7px;
            border-radius: 20px; background: rgba(99,102,241,.25); color: #818cf8;
        }

        /* ── MAIN LAYOUT ── */
        .main { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; min-width: 0; max-width: calc(100vw - 260px); overflow-x: hidden; }

        .topbar {
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226,232,240,.8);
            padding: 0 28px;
            height: 64px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 0 rgba(0,0,0,.04);
            box-sizing: border-box;
            max-width: 100%;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .topbar-title {
            font-size: 17px; font-weight: 700; color: var(--text);
        }
        .topbar-breadcrumb {
            font-size: 12px; color: var(--text-muted);
            display: flex; align-items: center; gap: 6px;
        }
        .topbar-breadcrumb i { font-size: 10px; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .topbar-company {
            font-size: 12px; color: var(--text-muted);
            padding: 6px 14px; background: var(--bg);
            border-radius: 20px; border: 1px solid var(--border);
            display: flex; align-items: center; gap: 6px;
        }
        .topbar-company i { color: var(--primary); }
        .topbar-date {
            font-size: 12px; color: var(--text-muted);
            padding: 6px 14px; background: var(--bg);
            border-radius: 20px; border: 1px solid var(--border);
        }
        .topbar-date-btn {
            font-size: 12px; font-weight: 600; color: var(--text);
            padding: 6px 14px; background: var(--bg);
            border-radius: 20px; border: 1px solid var(--border);
            display: flex; align-items: center; gap: 8px;
            cursor: pointer; transition: all .2s ease;
            text-decoration: none; font-family: 'Inter', sans-serif;
        }
        .topbar-date-btn:hover {
            border-color: var(--primary);
            background: #f5f3ff;
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(99,102,241,0.15);
        }
        .topbar-date-btn.is-custom {
            background: rgba(99,102,241,0.1);
            border-color: rgba(99,102,241,0.4);
            color: var(--primary);
        }
        .topbar-date-btn .date-badge-custom {
            font-size: 10px; font-weight: 700; background: var(--primary); color: #fff;
            padding: 1px 6px; border-radius: 10px; text-transform: uppercase;
        }

        .content { padding: 24px; flex: 1; min-width: 0; max-width: 100%; box-sizing: border-box; }

        /* ── CARDS ── */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: box-shadow .2s;
        }
        .card:hover { box-shadow: var(--shadow); }
        .card-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            background: linear-gradient(to right, #fafbff, #ffffff);
        }
        .card-header h3 {
            font-size: 14px; font-weight: 700; color: var(--text);
            display: flex; align-items: center; gap: 8px;
        }
        .card-header h3 i {
            width: 28px; height: 28px; border-radius: 7px;
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            color: var(--primary-dark);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 12px;
        }
        .card-body { padding: 22px; }

        /* ── STAT CARDS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px; margin-bottom: 28px;
        }
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 20px 22px;
            position: relative; overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: transform .2s, box-shadow .2s;
            cursor: default;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow); }
        .stat-card::before {
            content: ''; position: absolute;
            top: -20px; right: -20px;
            width: 100px; height: 100px; border-radius: 50%;
            opacity: .08;
        }
        .stat-card.s-indigo::before  { background: #6366f1; }
        .stat-card.s-emerald::before { background: #10b981; }
        .stat-card.s-amber::before   { background: #f59e0b; }
        .stat-card.s-red::before     { background: #ef4444; }
        .stat-card.s-violet::before  { background: #8b5cf6; }
        .stat-card.s-cyan::before    { background: #06b6d4; }
        .stat-card.s-rose::before    { background: #f43f5e; }
        .stat-card.s-teal::before    { background: #14b8a6; }

        .stat-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 14px; }
        .stat-icon {
            width: 46px; height: 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 19px; flex-shrink: 0;
        }
        .s-indigo  .stat-icon { background: linear-gradient(135deg,#6366f1,#818cf8); color:#fff; box-shadow:0 6px 16px rgba(99,102,241,.3); }
        .s-emerald .stat-icon { background: linear-gradient(135deg,#10b981,#34d399); color:#fff; box-shadow:0 6px 16px rgba(16,185,129,.3); }
        .s-amber   .stat-icon { background: linear-gradient(135deg,#f59e0b,#fbbf24); color:#fff; box-shadow:0 6px 16px rgba(245,158,11,.3); }
        .s-red     .stat-icon { background: linear-gradient(135deg,#ef4444,#f87171); color:#fff; box-shadow:0 6px 16px rgba(239,68,68,.3); }
        .s-violet  .stat-icon { background: linear-gradient(135deg,#8b5cf6,#a78bfa); color:#fff; box-shadow:0 6px 16px rgba(139,92,246,.3); }
        .s-cyan    .stat-icon { background: linear-gradient(135deg,#06b6d4,#22d3ee); color:#fff; box-shadow:0 6px 16px rgba(6,182,212,.3); }
        .s-rose    .stat-icon { background: linear-gradient(135deg,#f43f5e,#fb7185); color:#fff; box-shadow:0 6px 16px rgba(244,63,94,.3); }
        .s-teal    .stat-icon { background: linear-gradient(135deg,#14b8a6,#2dd4bf); color:#fff; box-shadow:0 6px 16px rgba(20,184,166,.3); }

        .stat-trend {
            font-size: 10px; font-weight: 600; padding: 3px 8px;
            border-radius: 20px;
        }
        .stat-label { font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 4px; }
        .stat-value { font-size: 26px; font-weight: 800; color: var(--text); letter-spacing: -.5px; }
        .stat-value small { font-size: 13px; font-weight: 600; }

        /* ── TABLES ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        thead tr { background: linear-gradient(to right, #f8faff, #f1f5f9); }
        th {
            font-weight: 700; font-size: 11px; text-transform: uppercase;
            letter-spacing: .6px; color: var(--text-muted);
            padding: 12px 16px; text-align: left;
            border-bottom: 2px solid var(--border); white-space: nowrap;
        }
        td { padding: 13px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tbody tr { transition: background .15s; }
        tbody tr:hover td { background: #f8faff; }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; border-radius: var(--radius-sm);
            border: none; cursor: pointer; font-size: 13px;
            font-weight: 600; text-decoration: none;
            transition: all .2s; white-space: nowrap;
            font-family: 'Inter', sans-serif;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 4px 12px rgba(99,102,241,.35);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            box-shadow: 0 6px 18px rgba(99,102,241,.45);
            transform: translateY(-1px);
        }
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff; box-shadow: 0 4px 12px rgba(16,185,129,.3);
        }
        .btn-success:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(16,185,129,.4); }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff; box-shadow: 0 4px 12px rgba(239,68,68,.25);
        }
        .btn-danger:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(239,68,68,.35); }
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff; box-shadow: 0 4px 12px rgba(245,158,11,.25);
        }
        .btn-outline {
            background: #fff; border: 1.5px solid var(--border);
            color: var(--text); box-shadow: var(--shadow-sm);
        }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); background: #faf5ff; }
        .btn-ghost { background: transparent; border: none; color: var(--text-muted); box-shadow: none; }
        .btn-ghost:hover { background: var(--bg); color: var(--text); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-icon { padding: 7px 9px; }

        /* ── BADGES ── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; letter-spacing: .2px;
        }
        .badge::before { content:''; width:5px; height:5px; border-radius:50%; }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-green::before  { background: #10b981; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }
        .badge-blue::before   { background: #3b82f6; }
        .badge-orange { background: #fef3c7; color: #92400e; }
        .badge-orange::before { background: #f59e0b; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-red::before    { background: #ef4444; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }
        .badge-purple::before { background: #8b5cf6; }
        .badge-gray   { background: #f1f5f9; color: #475569; }
        .badge-gray::before   { background: #94a3b8; }

        /* ── FORMS ── */
        .form-group { margin-bottom: 16px; }
        label {
            display: block; font-size: 12px; font-weight: 600;
            margin-bottom: 6px; color: var(--text);
            letter-spacing: .2px;
        }
        input, select, textarea {
            width: 100%; padding: 9px 13px;
            border: 1.5px solid var(--border); border-radius: var(--radius-sm);
            font-size: 13px; background: #fff; color: var(--text);
            transition: all .2s; font-family: 'Inter', sans-serif;
        }
        input:focus, select:focus, textarea:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99,102,241,.1);
        }
        input:hover, select:hover { border-color: #c7d2fe; }
        textarea { resize: vertical; min-height: 72px; }
        .form-row { display: grid; gap: 14px; }
        .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
        .form-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
        .form-hint { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        /* ── MODALS ── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(15,23,42,.55);
            backdrop-filter: blur(4px);
            z-index: 1000; display: none;
            align-items: center; justify-content: center; padding: 20px;
        }
        .modal-overlay.open { display: flex; animation: fadeIn .2s ease; }
        @keyframes fadeIn { from{opacity:0} to{opacity:1} }
        .modal {
            background: #fff; border-radius: 16px;
            width: 100%; max-width: 540px; max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 24px 80px rgba(0,0,0,.2);
            animation: slideUp .25s ease;
        }
        @keyframes slideUp { from{transform:translateY(20px);opacity:0} to{transform:translateY(0);opacity:1} }
        .modal-header {
            padding: 20px 24px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            background: linear-gradient(to right, #fafaff, #fff);
            border-radius: 16px 16px 0 0;
        }
        .modal-header h3 {
            font-size: 16px; font-weight: 700; color: var(--text);
            display: flex; align-items: center; gap: 10px;
        }
        .modal-header-icon {
            width: 34px; height: 34px; border-radius: 8px;
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            color: var(--primary-dark);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        }
        .modal-close {
            background: none; border: none; cursor: pointer;
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--text-muted); font-size: 16px; transition: all .2s;
        }
        .modal-close:hover { background: #fee2e2; color: var(--danger); }
        .modal-body { padding: 22px 24px; }
        .modal-footer {
            padding: 16px 24px; border-top: 1px solid var(--border);
            display: flex; justify-content: flex-end; gap: 10px;
            background: #fafafa; border-radius: 0 0 16px 16px;
        }

        /* ── TOAST ── */
        #toast-container {
            position: fixed; bottom: 24px; right: 24px;
            z-index: 9999; display: flex; flex-direction: column; gap: 10px;
        }
        .toast {
            padding: 14px 18px; border-radius: 12px;
            font-size: 13px; font-weight: 600;
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
            animation: toastIn .35s cubic-bezier(.34,1.56,.64,1);
            max-width: 340px; display: flex; align-items: center; gap: 10px;
            border-left: 4px solid transparent;
        }
        .toast.success { background: #fff; color: #065f46; border-left-color: #10b981; }
        .toast.success .toast-icon { color: #10b981; }
        .toast.error   { background: #fff; color: #991b1b; border-left-color: #ef4444; }
        .toast.error .toast-icon   { color: #ef4444; }
        .toast.info    { background: #fff; color: #1e40af; border-left-color: #3b82f6; }
        .toast.info .toast-icon    { color: #3b82f6; }
        .toast-icon { font-size: 16px; flex-shrink: 0; }
        @keyframes toastIn { from{transform:translateX(120%);opacity:0} to{transform:translateX(0);opacity:1} }

        /* ── MISC UTILITIES ── */
        .empty-state {
            text-align: center; padding: 60px 30px; color: var(--text-muted);
        }
        .empty-state .empty-icon {
            width: 72px; height: 72px; border-radius: 50%;
            background: linear-gradient(135deg, #ede9fe, #ddd6fe);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; color: var(--primary);
            margin: 0 auto 16px;
        }
        .empty-state p { font-size: 14px; font-weight: 500; margin-bottom: 4px; color: var(--text); }
        .empty-state small { font-size: 12px; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }
        .fw-600 { font-weight: 600; }
        .d-flex { display: flex; }
        .gap-1 { gap: 6px; }
        .gap-2 { gap: 10px; }
        .align-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .w-full { width: 100%; }
        .mt-1 { margin-top: 6px; }
        .mt-2 { margin-top: 12px; }
        .mt-3 { margin-top: 20px; }
        .text-muted { color: var(--text-muted); }
        .text-success { color: var(--success); }
        .text-danger  { color: var(--danger); }
        .text-primary { color: var(--primary); }

        /* ── PAGE HEADER ── */
        .page-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px; padding: 18px 24px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            border-radius: var(--radius);
            box-shadow: 0 8px 24px rgba(99,102,241,.3);
            color: #fff;
        }
        .page-header h2 { font-size: 20px; font-weight: 800; }
        .page-header p  { font-size: 13px; opacity: .85; margin-top: 2px; }

        /* ── DIVIDER ── */
        .divider { height: 1px; background: var(--border); margin: 16px 0; }

        /* ── CODE ── */
        code {
            background: #f1f5f9; color: #4f46e5;
            padding: 2px 7px; border-radius: 5px;
            font-size: 12px; font-family: 'Courier New', monospace; font-weight: 600;
        }

        /* ── PAGINATION ── */
        .pagination { display: flex; gap: 4px; flex-wrap: wrap; }
        .pagination .page-item .page-link {
            padding: 6px 12px; border-radius: 7px; border: 1px solid var(--border);
            color: var(--text-muted); font-size: 13px; font-weight: 500;
            text-decoration: none; transition: all .2s; background: #fff;
        }
        .pagination .page-item.active .page-link {
            background: var(--primary); border-color: var(--primary); color: #fff;
        }
        .pagination .page-item .page-link:hover { border-color: var(--primary); color: var(--primary); }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── SECTION DIVIDER in sidebar ── */
        .sidebar-divider {
            height: 1px; background: var(--sidebar-border);
            margin: 8px 16px;
        }

        /* ── MOBILE HAMBURGER ── */
        .hamburger {
            display: none; flex-direction: column; gap: 5px; cursor: pointer;
            background: none; border: none; padding: 6px; border-radius: 8px;
            transition: background .2s;
        }
        .hamburger:hover { background: rgba(99,102,241,.1); }
        .hamburger span {
            display: block; width: 22px; height: 2px;
            background: var(--text); border-radius: 2px;
            transition: all .3s ease;
        }
        .hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .hamburger.open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        .hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        .topbar-desktop-only { display: flex; align-items: center; gap: 8px; }

        /* ── SIDEBAR OVERLAY ── */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,.6); backdrop-filter: blur(3px);
            z-index: 199;
        }
        .sidebar-overlay.open { display: block; }

        /* ── RESPONSIVE BREAKPOINTS (100% MOBILE & TABLET OPTIMIZED) ── */
        @media (max-width: 768px) {
            html, body { width: 100%; max-width: 100vw; overflow-x: hidden; }

            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                max-width: 85vw;
                z-index: 2000;
                box-shadow: 6px 0 30px rgba(0,0,0,.4);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .sidebar.open { transform: translateX(0); }

            .main { margin-left: 0; min-height: 100vh; width: 100%; max-width: 100vw; overflow-x: hidden; box-sizing: border-box; }

            .topbar {
                padding: 0 12px;
                height: 56px;
                position: sticky;
                top: 0;
                z-index: 100;
                background: #ffffff;
                border-bottom: 1px solid var(--border);
                width: 100%;
                max-width: 100vw;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: space-between;
                overflow: hidden;
            }
            .topbar-left {
                display: flex;
                align-items: center;
                gap: 8px;
                min-width: 0;
                flex: 1;
            }
            .topbar-desktop-only { display: none !important; }
            .topbar-breadcrumb { display: none !important; }
            .topbar-date, .topbar-date-btn { display: none !important; }
            .topbar-company { display: none !important; }
            .topbar-right a[href*="onboard"] { display: none !important; }
            .hamburger { display: flex; flex-shrink: 0; }
            .topbar-title {
                font-size: 15px;
                font-weight: 700;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .topbar-right {
                display: flex;
                align-items: center;
                gap: 8px;
                flex-shrink: 0;
            }
            .topbar-live-sync-btn span { display: none; }
            .topbar-live-sync-btn { padding: 6px 10px; font-size: 11px; }

            #userMenuBtn {
                padding: 4px 6px 4px 4px !important;
                border-radius: 20px;
                gap: 0;
            }
            .user-name-label, .user-role-badge, #userMenuChevron { display: none !important; }

            .content {
                padding: 12px;
                width: 100%;
                max-width: 100vw;
                box-sizing: border-box;
                overflow-x: hidden;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 10px;
                width: 100%;
                box-sizing: border-box;
                margin-bottom: 16px;
            }
            .stat-card { padding: 14px; width: 100%; box-sizing: border-box; }
            .stat-value { font-size: 18px; }
            .stat-icon { width: 36px; height: 36px; font-size: 15px; }

            .dashboard-grid {
                grid-template-columns: 1fr !important;
                gap: 14px;
                width: 100%;
                box-sizing: border-box;
            }

            .form-row.cols-2,
            .form-row.cols-3 { grid-template-columns: 1fr; gap: 10px; }

            .card {
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
                margin-bottom: 14px;
            }
            .card-header { padding: 12px 14px; flex-wrap: wrap; gap: 8px; }
            .card-header h3 { font-size: 14.5px; }
            .card-body { padding: 12px; width: 100%; box-sizing: border-box; }

            .table-wrap {
                width: 100%;
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin-bottom: 8px;
                box-sizing: border-box;
            }
            table { font-size: 12px; width: 100%; }
            th, td { padding: 8px 10px; }

            .modal-overlay {
                padding: 10px;
                align-items: center;
                justify-content: center;
            }
            .modal {
                width: 96%;
                max-width: 520px;
                max-height: 88vh;
                margin: auto;
                border-radius: 16px;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }
            .modal-header { padding: 14px 18px; }
            .modal-body { padding: 16px 18px; }
            .modal-footer { padding: 12px 18px; flex-wrap: wrap; }

            input, select, textarea {
                font-size: 15px !important; /* Prevents auto-zoom on iOS */
            }

            #toast-container { bottom: 16px; right: 10px; left: 10px; }
            .toast { max-width: 100%; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr 1fr !important; gap: 8px; }
            .stat-card { padding: 12px; }
            .stat-value { font-size: 16px; }
            .stat-label { font-size: 11px; }
            .stat-icon { width: 32px; height: 32px; font-size: 13px; }

            .btn { padding: 7px 11px; font-size: 12px; }
            .btn-sm { padding: 5px 9px; font-size: 11px; }

            .topbar-live-sync-btn span { display: none; }
            .topbar-live-sync-btn { padding: 6px 10px; }
        }

        /* ── SPA PROGRESS BAR & LIVE SYNC ── */
        #spaProgressBar {
            position: fixed; top: 0; left: 0; width: 0%; height: 3px;
            background: linear-gradient(90deg, #6366f1, #10b981, #f59e0b);
            z-index: 99999; transition: width .2s ease, opacity .3s ease; opacity: 0; pointer-events: none;
        }
        .topbar-live-sync-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 6px 14px; background: rgba(16,185,129,0.08);
            border: 1px solid rgba(16,185,129,0.3); border-radius: 20px;
            font-size: 12px; font-weight: 600; color: #047857;
            cursor: pointer; transition: all .2s; font-family: 'Inter', sans-serif;
            text-decoration: none;
        }
        .topbar-live-sync-btn:hover {
            background: rgba(16,185,129,0.15); border-color: #10b981; transform: translateY(-1px);
        }
        .live-pulse-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: #10b981; box-shadow: 0 0 6px #10b981;
            animation: pulse-dot 1.5s infinite;
        }
        .sync-spinning {
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div id="spaProgressBar"></div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<nav class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <i class="fa fa-industry"></i>
            </div>
            <div class="sidebar-logo-text">
                <h2>Shree Giriraj<br>Poly Plast</h2>
                <small>ERP &amp; Billing System</small>
            </div>
        </div>
        <div class="sidebar-tagline">System Active · Ahmedabad, Gujarat</div>
    </div>

    <div class="sidebar-section">Core &amp; Overview</div>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-gauge-high"></i></span>
        <span class="nav-label">Dashboard</span>
    </a>
    <a href="{{ route('invoices.create') }}" class="{{ request()->routeIs('invoices.create') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-cash-register"></i></span>
        <span class="nav-label">POS</span>
    </a>
    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-chart-line"></i></span>
        <span class="nav-label">Reports</span>
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Masters &amp; Directory</div>
    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-tag"></i></span>
        <span class="nav-label">Products</span>
    </a>
    <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-layer-group"></i></span>
        <span class="nav-label">Categories</span>
    </a>
    <a href="{{ route('units.index') }}" class="{{ request()->routeIs('units.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-ruler-combined"></i></span>
        <span class="nav-label">Units &amp; Conversion</span>
    </a>
    <a href="{{ route('warehouses.index') }}" class="{{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-warehouse"></i></span>
        <span class="nav-label">Warehouses</span>
    </a>
    <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-users"></i></span>
        <span class="nav-label">Customers</span>
    </a>
    <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-handshake"></i></span>
        <span class="nav-label">Suppliers</span>
    </a>
    <a href="{{ route('transporters.index') }}" class="{{ request()->routeIs('transporters.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-truck"></i></span>
        <span class="nav-label">Transporters</span>
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Inventory &amp; Stock</div>
    <a href="{{ route('inventory.dashboard') }}" class="{{ request()->routeIs('inventory.dashboard') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-boxes-stacked"></i></span>
        <span class="nav-label">Stock Dashboard</span>
    </a>
    <a href="{{ route('inventory.ledger') }}" class="{{ request()->routeIs('inventory.ledger') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-book-open"></i></span>
        <span class="nav-label">Stock Ledger</span>
    </a>
    <a href="{{ route('inventory.low-stock') }}" class="{{ request()->routeIs('inventory.low-stock') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-triangle-exclamation"></i></span>
        <span class="nav-label">Low Stock Alerts</span>
    </a>
    <a href="{{ route('inventory.valuation') }}" class="{{ request()->routeIs('inventory.valuation') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-scale-balanced"></i></span>
        <span class="nav-label">Stock Valuation</span>
    </a>
    <a href="{{ route('inventory.adjustments.index') }}" class="{{ request()->routeIs('inventory.adjustments.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-sliders"></i></span>
        <span class="nav-label">Stock Adjustments</span>
    </a>
    <a href="{{ route('inventory.transfers.index') }}" class="{{ request()->routeIs('inventory.transfers.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-truck-ramp-box"></i></span>
        <span class="nav-label">Stock Transfers</span>
    </a>
    <a href="{{ route('materials.index') }}" class="{{ request()->routeIs('materials.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-cubes"></i></span>
        <span class="nav-label">Raw Materials</span>
    </a>
    <a href="{{ route('material-transactions.index') }}" class="{{ request()->routeIs('material-transactions.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-circle-arrow-down"></i></span>
        <span class="nav-label">Material In / Out</span>
    </a>
    <a href="{{ route('production.index') }}" class="{{ request()->routeIs('production.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-gears"></i></span>
        <span class="nav-label">Production &amp; BOM</span>
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Tooling &amp; Plant Assets</div>
    <a href="{{ route('dyes.index') }}" class="{{ request()->routeIs('dyes.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-shapes"></i></span>
        <span class="nav-label">Dyes &amp; Moulds (ડાઈ)</span>
    </a>
    <a href="{{ route('factory-assets.index') }}" class="{{ request()->routeIs('factory-assets.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-industry"></i></span>
        <span class="nav-label">Plant Machinery (મશીન)</span>
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Staff &amp; Payroll</div>
    <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-users-gear"></i></span>
        <span class="nav-label">Staff Directory (કર્મચારી)</span>
    </a>
    <a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-clipboard-user"></i></span>
        <span class="nav-label">Attendance (હાજરી)</span>
    </a>
    <a href="{{ route('employee-advances.index') }}" class="{{ request()->routeIs('employee-advances.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-hand-holding-dollar"></i></span>
        <span class="nav-label">Upad / Advances (ઉપાડ)</span>
    </a>
    <a href="{{ route('payroll.index') }}" class="{{ request()->routeIs('payroll.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-file-invoice-dollar"></i></span>
        <span class="nav-label">Salary &amp; Payroll (પગાર)</span>
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Job Work Module</div>
    <a href="{{ route('jobworks.dashboard') }}" class="{{ request()->routeIs('jobworks.dashboard') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-chart-pie"></i></span>
        <span class="nav-label">JW Dashboard</span>
    </a>
    <a href="{{ route('jobworks.create') }}" class="{{ request()->routeIs('jobworks.create') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-circle-plus"></i></span>
        <span class="nav-label">New Job Work</span>
    </a>
    <a href="{{ route('jobworks.index') }}" class="{{ request()->routeIs('jobworks.index') || request()->routeIs('jobworks.show') || request()->routeIs('jobworks.edit') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-scale-balanced"></i></span>
        <span class="nav-label">Job Work Orders</span>
    </a>
    <a href="{{ route('jobworks.clients.index') }}" class="{{ request()->routeIs('jobworks.clients.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-users"></i></span>
        <span class="nav-label">Job Work Clients</span>
    </a>
    <a href="{{ route('jobworks.reports') }}" class="{{ request()->routeIs('jobworks.reports') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-file-invoice-dollar"></i></span>
        <span class="nav-label">JW Reports</span>
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Accounts &amp; Finance</div>
    <a href="{{ route('ledger.index') }}" class="{{ request()->routeIs('ledger.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-money-bill-transfer"></i></span>
        <span class="nav-label">Payment</span>
    </a>
    <a href="{{ route('ledger.index') }}" class="{{ request()->routeIs('ledger.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-receipt"></i></span>
        <span class="nav-label">Receipt</span>
    </a>

    @auth
    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Download Apps</div>
    <a href="{{ asset('downloads/Shree-Giriraj-ERP-Desktop-Setup.zip') }}" download title="Download Windows Desktop App (Setup ZIP)">
        <span class="nav-icon"><i class="fa fa-desktop"></i></span>
        <span class="nav-label">Desktop App (.zip)</span>
        <span class="nav-badge" style="background:rgba(99,102,241,.2);color:#818cf8;">Windows 64-bit</span>
    </a>
    <a href="http://192.168.1.13:8080" target="_blank" title="Download Mobile App APK & Portal">
        <span class="nav-icon"><i class="fa fa-mobile-screen-button"></i></span>
        <span class="nav-label">Mobile App Portal</span>
        <span class="nav-badge" style="background:rgba(16,185,129,.2);color:#34d399;">Android APK</span>
    </a>

    @if(auth()->user()->isAdmin())
    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Administration</div>
    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-users-gear"></i></span>
        <span class="nav-label">User Management</span>
    </a>
    @endif

    <!-- Sidebar User Footer -->
    <div style="margin-top:auto;padding:16px 14px 20px;border-top:1px solid rgba(255,255,255,.06);">
        <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(255,255,255,.04);border-radius:10px;border:1px solid rgba(255,255,255,.06);margin-bottom:10px;">
            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:14px;font-weight:700;color:#fff;">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12.5px;font-weight:600;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                <div style="font-size:10px;color:{{ auth()->user()->isAdmin() ? '#818cf8' : '#10b981' }};font-weight:600;text-transform:uppercase;letter-spacing:.5px;">
                    <i class="fa {{ auth()->user()->isAdmin() ? 'fa-user-shield' : 'fa-user-tie' }}" style="margin-right:3px;"></i>
                    {{ ucfirst(auth()->user()->role) }}
                </div>
            </div>
        </div>
        <a href="{{ route('change-password') }}" style="display:flex;align-items:center;gap:9px;padding:8px 12px;color:#64748b;text-decoration:none;font-size:12.5px;font-weight:500;border-radius:8px;transition:all .2s;margin-bottom:4px;" onmouseover="this.style.background='rgba(255,255,255,.06)';this.style.color='#94a3b8'" onmouseout="this.style.background='';this.style.color='#64748b'">
            <i class="fa fa-key" style="font-size:12px;width:14px;text-align:center;"></i>
            Change Password
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="width:100%;display:flex;align-items:center;gap:9px;padding:8px 12px;color:#ef4444;background:none;border:none;font-size:12.5px;font-weight:500;border-radius:8px;cursor:pointer;font-family:inherit;transition:all .2s;" onmouseover="this.style.background='rgba(239,68,68,.08)'" onmouseout="this.style.background=''">
                <i class="fa fa-right-from-bracket" style="font-size:12px;width:14px;text-align:center;"></i>
                Logout
            </button>
        </form>
    </div>
    @endauth
</nav>

<div class="main">
    <div class="topbar">
        <div class="topbar-left">
            <button class="hamburger" id="hamburgerBtn" onclick="toggleSidebar()" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <div>
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="topbar-breadcrumb">
                    <span>Shree Giriraj Poly Plast</span>
                    <i class="fa fa-chevron-right"></i>
                    <span style="color:var(--primary)">@yield('page-title', 'Dashboard')</span>
                </div>
            </div>
        </div>
        <div class="topbar-right">
            <button type="button" class="topbar-live-sync-btn" id="liveSyncBtn" onclick="triggerLiveSync()" title="Auto-Sync Active (Loads updates without full page refresh)">
                <span class="live-pulse-dot" id="liveSyncDot"></span>
                <span id="liveSyncText">Live Sync</span>
                <i class="fa fa-rotate" id="liveSyncIcon" style="font-size:11px;"></i>
            </button>
            <div class="topbar-desktop-only">
                <a href="{{ route('onboard.index') }}" class="btn btn-outline btn-sm" style="font-size:11px; border-color:var(--primary); color:var(--primary);">
                    <i class="fa fa-wand-magic-sparkles"></i> AI Setup Configurator
                </a>
                @php
                    $currentFilter = \App\Http\Controllers\DashboardController::resolveDateFilter();
                @endphp
                <button type="button" class="topbar-date-btn {{ $currentFilter['is_filtered'] ? 'is-custom' : '' }}" onclick="openDateSelectorModal()" id="topbarDateBtn" title="Click to filter ERP data by date range or single day">
                    <i class="fa fa-calendar-days" style="color:var(--primary)"></i>
                    <span id="topbar-date-text">{{ $currentFilter['label'] }}</span>
                    @if($currentFilter['is_filtered'])
                        <span class="date-badge-custom">Filter Active</span>
                    @endif
                    <i class="fa fa-chevron-down" style="font-size:10px; opacity:0.6;"></i>
                </button>
                <a href="{{ route('branches.index') }}" class="topbar-company" style="text-decoration:none;" title="Click to manage multi-location branches">
                    <i class="fa fa-location-dot"></i> {{ session('current_branch', 'Ahmedabad, Gujarat') }}
                </a>
            </div>
            @auth
            <div style="position:relative;" id="userMenuWrapper">
                <button type="button" 
                    id="userMenuBtn" 
                    onclick="toggleUserDropdown(event)"
                    style="display:flex;align-items:center;gap:8px;padding:5px 12px;background:var(--bg-surface, #ffffff);border:1px solid var(--border);border-radius:20px;font-size:12px;color:var(--text-muted);cursor:pointer;transition:all .2s;box-shadow:0 1px 3px rgba(0,0,0,0.05);"
                    title="Click to switch active user account or manage profile">
                    <div style="width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#8b5cf6);display:flex;align-items:center;justify-content:center;box-shadow:0 1px 3px rgba(99,102,241,0.3);">
                        <span style="font-size:10px;font-weight:700;color:#fff;">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
                    </div>
                    <span class="user-name-label" style="font-weight:600;color:var(--text);">{{ auth()->user()->name }}</span>
                    <span class="user-role-badge" style="padding:2px 7px;background:{{ auth()->user()->isAdmin() ? 'rgba(99,102,241,.12)' : 'rgba(16,185,129,.12)' }};color:{{ auth()->user()->isAdmin() ? 'var(--primary)' : 'var(--accent2)' }};border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;">{{ auth()->user()->role }}</span>
                    <i class="fa fa-chevron-down" id="userMenuChevron" style="font-size:9px;color:var(--text-muted);margin-left:2px;transition:transform .2s;"></i>
                </button>

                <!-- Dropdown Menu -->
                <div id="userDropdownMenu" style="display:none;position:absolute;right:0;top:calc(100% + 8px);width:290px;background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 15px 35px rgba(0,0,0,0.18), 0 5px 15px rgba(0,0,0,0.08);z-index:1050;overflow:hidden;">
                    <!-- User Header -->
                    <div style="padding:14px 16px;background:linear-gradient(135deg,#f8fafc,#f1f5f9);border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:10px;">
                        <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--primary),#8b5cf6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px;font-weight:700;flex-shrink:0;">
                            {{ strtoupper(substr(auth()->user()->name,0,1)) }}
                        </div>
                        <div style="overflow:hidden;flex:1;">
                            <div style="font-weight:700;font-size:13.5px;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</div>
                            <div style="font-size:11.5px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->email }}</div>
                        </div>
                        <span style="padding:2px 7px;background:{{ auth()->user()->isAdmin() ? 'rgba(99,102,241,.15)' : 'rgba(16,185,129,.15)' }};color:{{ auth()->user()->isAdmin() ? '#4338ca' : '#047857' }};border-radius:12px;font-size:10px;font-weight:800;text-transform:uppercase;">
                            {{ auth()->user()->role }}
                        </span>
                    </div>

                    <!-- Switch Account Section -->
                    <div style="padding:10px 14px 6px 14px;border-bottom:1px solid #f1f5f9;">
                        <div style="font-size:11px;font-weight:700;text-transform:uppercase;color:#94a3b8;letter-spacing:0.5px;margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;">
                            <span><i class="fa fa-users-viewfinder" style="color:var(--primary);"></i> Switch User Account</span>
                            <span style="font-size:10px;font-weight:600;color:var(--primary);">ઝડપી બદલો</span>
                        </div>
                        @php
                            $availableUsers = \App\Models\User::where('is_active', true)->orderBy('role')->orderBy('name')->get();
                        @endphp
                        <div style="max-height:180px;overflow-y:auto;display:flex;flex-direction:column;gap:4px;">
                            @foreach($availableUsers as $u)
                            <div onclick="switchUserAccount({{ $u->id }})"
                                style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:8px;cursor:pointer;transition:background .15s;{{ $u->id === auth()->id() ? 'background:#f0fdf4;border:1px solid #bbf7d0;' : 'background:#ffffff;' }}"
                                onmouseover="if({{ $u->id }} !== {{ auth()->id() }}) this.style.background='#f8fafc';"
                                onmouseout="if({{ $u->id }} !== {{ auth()->id() }}) this.style.background='#ffffff';">
                                <div style="width:26px;height:26px;border-radius:50%;background:{{ $u->isAdmin() ? '#6366f1' : '#10b981' }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($u->name,0,1)) }}
                                </div>
                                <div style="flex:1;overflow:hidden;">
                                    <div style="font-size:12px;font-weight:{{ $u->id === auth()->id() ? '700' : '600' }};color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $u->name }}
                                    </div>
                                    <div style="font-size:10px;color:#64748b;text-transform:uppercase;font-weight:600;">
                                        {{ $u->role }}
                                    </div>
                                </div>
                                @if($u->id === auth()->id())
                                    <span style="font-size:10px;font-weight:700;color:#16a34a;display:flex;align-items:center;gap:3px;background:#dcfce7;padding:2px 6px;border-radius:6px;">
                                        <i class="fa fa-check"></i> Active
                                    </span>
                                @else
                                    <span style="font-size:11px;color:var(--primary);font-weight:600;opacity:0.8;">
                                        <i class="fa fa-arrow-right-to-bracket"></i>
                                    </span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="padding:6px 8px;background:#fafafa;">
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('users.index') }}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;color:#334155;text-decoration:none;font-size:12px;font-weight:500;border-radius:6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                            <i class="fa fa-users-gear" style="color:var(--primary);width:16px;"></i> Manage All Users
                        </a>
                        @endif
                        <a href="{{ route('change-password') }}" style="display:flex;align-items:center;gap:8px;padding:8px 10px;color:#334155;text-decoration:none;font-size:12px;font-weight:500;border-radius:6px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                            <i class="fa fa-key" style="color:#f59e0b;width:16px;"></i> Change Password
                        </a>
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                            @csrf
                            <button type="submit" style="width:100%;text-align:left;background:none;border:none;display:flex;align-items:center;gap:8px;padding:8px 10px;color:#ef4444;font-size:12px;font-weight:600;border-radius:6px;cursor:pointer;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='transparent'">
                                <i class="fa fa-arrow-right-from-bracket" style="width:16px;"></i> Sign Out / Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
        </div>
    </div>
    <div class="content">
        @yield('content')
        <div id="spaSectionScripts" style="display:none;">
            @yield('scripts')
        </div>
    </div>
</div>

<div id="toast-container"></div>

<!-- Date Filter Modal -->
<div class="modal-overlay" id="dateSelectorModal">
    <div class="modal" style="max-width:480px; background:#ffffff; box-shadow: 0 25px 60px rgba(0,0,0,0.35); border: 1px solid #e2e8f0; border-radius:20px; overflow:hidden;">
        <div class="modal-header" style="background:#ffffff; border-bottom:1px solid #e2e8f0; padding:18px 24px;">
            <h3 style="font-size:16px; font-weight:700; color:#1e293b; margin:0; display:flex; align-items:center; gap:10px;">
                <div class="modal-header-icon" style="background:#ede9fe; color:#6366f1; width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                    <i class="fa fa-filter"></i>
                </div>
                <span>ERP Date Filter & Working Date</span>
            </h3>
            <button class="modal-close" onclick="closeModal('dateSelectorModal')">&times;</button>
        </div>

        <div class="modal-body" style="padding:20px 24px; background:#ffffff;">
            <!-- Tabs -->
            <div style="display:flex; background:#f1f5f9; padding:4px; border-radius:10px; margin-bottom:18px;">
                <button type="button" class="date-tab-btn active" id="tabBtnPresets" onclick="switchDateTab('presets')" style="flex:1; padding:8px 10px; font-size:12.5px; font-weight:600; border:none; border-radius:7px; background:#ffffff; color:#4338ca; box-shadow:0 1px 3px rgba(0,0,0,0.08); cursor:pointer; transition:all .2s;">
                    <i class="fa fa-bolt"></i> Presets
                </button>
                <button type="button" class="date-tab-btn" id="tabBtnSingle" onclick="switchDateTab('single')" style="flex:1; padding:8px 10px; font-size:12.5px; font-weight:600; border:none; border-radius:7px; background:transparent; color:#64748b; cursor:pointer; transition:all .2s;">
                    <i class="fa fa-calendar-day"></i> Single Day
                </button>
                <button type="button" class="date-tab-btn" id="tabBtnRange" onclick="switchDateTab('range')" style="flex:1; padding:8px 10px; font-size:12.5px; font-weight:600; border:none; border-radius:7px; background:transparent; color:#64748b; cursor:pointer; transition:all .2s;">
                    <i class="fa fa-calendar-week"></i> Date Range
                </button>
            </div>

            <!-- TAB 1: PRESETS -->
            <div id="dateTabPresets">
                <p style="font-size:12.5px; color:#64748b; margin-bottom:14px;">Select a standard date period to filter dashboard analytics & reports:</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
                    <button type="button" class="preset-choice-btn {{ ($currentFilter['preset'] === 'today') ? 'active-preset' : '' }}" onclick="applyPreset('today')">
                        <i class="fa fa-sun" style="color:#eab308; font-size:16px;"></i>
                        <div style="text-align:left;">
                            <div style="font-weight:700; font-size:13px; color:#1e293b;">Today</div>
                            <div style="font-size:11px; color:#94a3b8;">{{ date('d M Y') }}</div>
                        </div>
                    </button>

                    <button type="button" class="preset-choice-btn {{ ($currentFilter['preset'] === 'yesterday') ? 'active-preset' : '' }}" onclick="applyPreset('yesterday')">
                        <i class="fa fa-clock-rotate-left" style="color:#6366f1; font-size:16px;"></i>
                        <div style="text-align:left;">
                            <div style="font-weight:700; font-size:13px; color:#1e293b;">Yesterday</div>
                            <div style="font-size:11px; color:#94a3b8;">{{ date('d M Y', strtotime('-1 day')) }}</div>
                        </div>
                    </button>

                    <button type="button" class="preset-choice-btn {{ ($currentFilter['preset'] === 'this_month') ? 'active-preset' : '' }}" onclick="applyPreset('this_month')">
                        <i class="fa fa-calendar-check" style="color:#10b981; font-size:16px;"></i>
                        <div style="text-align:left;">
                            <div style="font-weight:700; font-size:13px; color:#1e293b;">This Month</div>
                            <div style="font-size:11px; color:#94a3b8;">{{ date('M Y') }}</div>
                        </div>
                    </button>

                    <button type="button" class="preset-choice-btn {{ ($currentFilter['preset'] === 'last_month') ? 'active-preset' : '' }}" onclick="applyPreset('last_month')">
                        <i class="fa fa-calendar-minus" style="color:#f97316; font-size:16px;"></i>
                        <div style="text-align:left;">
                            <div style="font-weight:700; font-size:13px; color:#1e293b;">Last Month</div>
                            <div style="font-size:11px; color:#94a3b8;">{{ date('M Y', strtotime('first day of last month')) }}</div>
                        </div>
                    </button>

                    <button type="button" class="preset-choice-btn {{ ($currentFilter['preset'] === 'this_year') ? 'active-preset' : '' }}" onclick="applyPreset('this_year')">
                        <i class="fa fa-chart-line" style="color:#3b82f6; font-size:16px;"></i>
                        <div style="text-align:left;">
                            <div style="font-weight:700; font-size:13px; color:#1e293b;">This Year</div>
                            <div style="font-size:11px; color:#94a3b8;">{{ date('Y') }}</div>
                        </div>
                    </button>

                    <button type="button" class="preset-choice-btn {{ ($currentFilter['preset'] === 'last_year') ? 'active-preset' : '' }}" onclick="applyPreset('last_year')">
                        <i class="fa fa-landmark" style="color:#8b5cf6; font-size:16px;"></i>
                        <div style="text-align:left;">
                            <div style="font-weight:700; font-size:13px; color:#1e293b;">Last Year</div>
                            <div style="font-size:11px; color:#94a3b8;">{{ date('Y', strtotime('-1 year')) }}</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- TAB 2: SINGLE DAY -->
            <div id="dateTabSingle" style="display:none;">
                <p style="font-size:12.5px; color:#64748b; margin-bottom:14px;">Choose a specific date for daily operations & bill creation:</p>
                <form id="singleDateForm" onsubmit="submitSingleDate(event)">
                    <div class="form-group" style="margin-bottom:18px;">
                        <label style="font-size:13px; font-weight:600; color:#1e293b; margin-bottom:6px; display:block;">Pick Date</label>
                        <input type="date" id="filterSingleDateInput" value="{{ $currentFilter['date_from'] ?: session('working_date', date('Y-m-d')) }}" style="width:100%; padding:11px 14px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:14px; font-weight:600; color:#0f172a; outline:none;" required>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('dateSelectorModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-check"></i> Set Working Day</button>
                    </div>
                </form>
            </div>

            <!-- TAB 3: DATE RANGE -->
            <div id="dateTabRange" style="display:none;">
                <p style="font-size:12.5px; color:#64748b; margin-bottom:14px;">Select custom Start Date and End Date range:</p>
                <form id="rangeDateForm" onsubmit="submitRangeDate(event)">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:12.5px; font-weight:600; color:#1e293b; margin-bottom:6px; display:block;">From Date</label>
                            <input type="date" id="filterRangeFromInput" value="{{ $currentFilter['date_from'] ?: date('Y-m-01') }}" style="width:100%; padding:10px 12px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:13.5px; font-weight:600; color:#0f172a; outline:none;" required>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:12.5px; font-weight:600; color:#1e293b; margin-bottom:6px; display:block;">To Date</label>
                            <input type="date" id="filterRangeToInput" value="{{ $currentFilter['date_to'] ?: date('Y-m-d') }}" style="width:100%; padding:10px 12px; border:1.5px solid #cbd5e1; border-radius:10px; font-size:13.5px; font-weight:600; color:#0f172a; outline:none;" required>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        <button type="button" class="btn btn-ghost btn-sm" onclick="closeModal('dateSelectorModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Apply Range</button>
                    </div>
                </form>
            </div>

            <!-- Footer Action -->
            <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; padding-top:14px; border-top:1px solid #f1f5f9; margin-top:10px;">
                @if($currentFilter['is_filtered'])
                <button type="button" class="btn btn-danger btn-sm" onclick="resetWorkingDate()"><i class="fa fa-rotate-left"></i> Reset to Default</button>
                @else
                <span style="font-size:12px; color:#94a3b8;">Status: Default (Today)</span>
                @endif
                <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('dateSelectorModal')">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.preset-choice-btn {
    display: flex; align-items: center; gap: 10px; padding: 10px 14px;
    border-radius: 12px; border: 1.5px solid #e2e8f0; background: #f8fafc;
    cursor: pointer; transition: all .2s; font-family: 'Inter', sans-serif;
}
.preset-choice-btn:hover {
    background: #f1f5f9; border-color: #cbd5e1; transform: translateY(-1px);
}
.preset-choice-btn.active-preset {
    background: #ede9fe; border-color: #6366f1; box-shadow: 0 0 0 1px #6366f1;
}
</style>

<script>
    function openDateSelectorModal() {
        openModal('dateSelectorModal');
    }

    function switchDateTab(tabName) {
        document.getElementById('dateTabPresets').style.display = (tabName === 'presets') ? 'block' : 'none';
        document.getElementById('dateTabSingle').style.display  = (tabName === 'single') ? 'block' : 'none';
        document.getElementById('dateTabRange').style.display   = (tabName === 'range') ? 'block' : 'none';

        const btnPresets = document.getElementById('tabBtnPresets');
        const btnSingle  = document.getElementById('tabBtnSingle');
        const btnRange   = document.getElementById('tabBtnRange');

        const activeStyle = { bg: '#ffffff', color: '#4338ca', shadow: '0 1px 3px rgba(0,0,0,0.08)' };
        const inactiveStyle = { bg: 'transparent', color: '#64748b', shadow: 'none' };

        [
            { btn: btnPresets, active: tabName === 'presets' },
            { btn: btnSingle,  active: tabName === 'single' },
            { btn: btnRange,   active: tabName === 'range' }
        ].forEach(t => {
            t.btn.style.background = t.active ? activeStyle.bg : inactiveStyle.bg;
            t.btn.style.color      = t.active ? activeStyle.color : inactiveStyle.color;
            t.btn.style.boxShadow  = t.active ? activeStyle.shadow : inactiveStyle.shadow;
        });
    }

    function applyPreset(presetName) {
        fetch('{{ route('working-date.set') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ type: 'preset', preset: presetName }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Applied ' + res.label, 'success');
                setTimeout(() => window.location.reload(), 250);
            }
        })
        .catch(() => showToast('Error applying filter', 'error'));
    }

    function submitSingleDate(e) {
        e.preventDefault();
        const dateVal = document.getElementById('filterSingleDateInput').value;
        if (!dateVal) return;

        fetch('{{ route('working-date.set') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ type: 'single', single_date: dateVal }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Working day set to ' + res.label, 'success');
                setTimeout(() => window.location.reload(), 250);
            }
        })
        .catch(() => showToast('Error setting date', 'error'));
    }

    function submitRangeDate(e) {
        e.preventDefault();
        const fromVal = document.getElementById('filterRangeFromInput').value;
        const toVal   = document.getElementById('filterRangeToInput').value;

        fetch('{{ route('working-date.set') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ type: 'range', date_from: fromVal, date_to: toVal }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Date range applied: ' + res.label, 'success');
                setTimeout(() => window.location.reload(), 250);
            }
        })
        .catch(() => showToast('Error applying range', 'error'));
    }

    function resetWorkingDate() {
        fetch('{{ route('working-date.reset') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Date filter reset to Today', 'success');
                setTimeout(() => window.location.reload(), 250);
            }
        })
        .catch(() => showToast('Error resetting filter', 'error'));
    }

    /* ── Toast ── */
    function showToast(message, type = 'success') {
        const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info' };
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast ${type === 'error' ? 'error' : type === 'info' ? 'info' : 'success'}`;
        toast.innerHTML = `<i class="fa ${icons[type] || icons.success} toast-icon"></i><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.transition = 'all .3s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(60px)';
            setTimeout(() => toast.remove(), 320);
        }, 3500);
    }

    /* ── Modal ── */
    function openModal(id) { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open');
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
        }
    });

    /* ── AJAX Form Submit with Double-Click Protection ── */
    function submitForm(formEl, url, method, onSuccess) {
        if (!formEl) return;
        if (formEl.dataset.submitting === 'true') {
            console.warn('Submission already in progress. Ignoring duplicate click.');
            return;
        }
        formEl.dataset.submitting = 'true';

        const btn = formEl.querySelector('[type=submit]');
        const origText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.style.pointerEvents = 'none';
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        }

        const hasFiles = !!formEl.querySelector('input[type="file"]') || formEl.getAttribute('enctype') === 'multipart/form-data';

        let fetchMethod = method;
        let body;
        let headers = {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        };

        if (hasFiles) {
            const formData = new FormData(formEl);
            if (method.toUpperCase() === 'PUT' || method.toUpperCase() === 'PATCH') {
                if (!formData.has('_method')) {
                    formData.append('_method', method.toUpperCase());
                }
                fetchMethod = 'POST';
            }
            body = formData;
        } else {
            const data = {};
            new FormData(formEl).forEach((v, k) => { data[k] = v; });
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(data);
        }

        fetch(url, {
            method: fetchMethod,
            headers: headers,
            body: body,
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, 'success');
                // Close modal immediately
                document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
                formEl.reset();
                if (onSuccess) {
                    onSuccess(res);
                } else {
                    // Instantly update table data without page refresh!
                    triggerLiveSync(true, true);
                }
            } else {
                showToast(res.message || 'An error occurred.', 'error');
            }
        })
        .catch(() => {
            showToast('Network error. Please try again.', 'error');
        })
        .finally(() => {
            delete formEl.dataset.submitting;
            if (btn) {
                btn.disabled = false;
                btn.style.pointerEvents = '';
                btn.innerHTML = origText;
            }
        });
    }

    /* ── Delete Record ── */
    function deleteRecord(url, label, btnEl) {
        if (!confirm(`Delete this ${label}?\n\nThis action cannot be undone.`)) return;
        
        // Optimistic UI update: fade out row immediately
        const tr = btnEl ? btnEl.closest('tr') : null;
        if (tr) {
            tr.style.transition = 'all 0.3s ease';
            tr.style.opacity = '0.3';
        }

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message, 'success');
                if (tr) {
                    tr.style.transform = 'scaleY(0)';
                    setTimeout(() => tr.remove(), 250);
                }
                triggerLiveSync(true, true);
            } else {
                if (tr) tr.style.opacity = '1';
                showToast(res.message, 'error');
            }
        })
        .catch(() => {
            if (tr) tr.style.opacity = '1';
            showToast('Network error. Could not delete.', 'error');
        });
    }

    /* ── Mobile Sidebar ── */
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const btn     = document.getElementById('hamburgerBtn');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
        btn.classList.toggle('open');
        document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
    }
    // Close sidebar when a nav link is clicked (mobile)
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar.classList.contains('open')) toggleSidebar();
            }
        });
    });
    // Close on resize to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            document.querySelector('.sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('open');
            document.getElementById('hamburgerBtn').classList.remove('open');
            document.body.style.overflow = '';
        }
    });

    /* ── User Switcher Dropdown ── */
    function toggleUserDropdown(event) {
        if (event) event.stopPropagation();
        const menu = document.getElementById('userDropdownMenu');
        const chevron = document.getElementById('userMenuChevron');
        if (!menu) return;
        const isShown = menu.style.display === 'block';
        menu.style.display = isShown ? 'none' : 'block';
        if (chevron) {
            chevron.style.transform = isShown ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('userMenuWrapper');
        const menu = document.getElementById('userDropdownMenu');
        const chevron = document.getElementById('userMenuChevron');
        if (menu && wrapper && !wrapper.contains(e.target)) {
            menu.style.display = 'none';
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    });

    async function switchUserAccount(userId) {
        try {
            const res = await fetch('{{ route("users.switch") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ user_id: userId })
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                showToast(data.message || 'Error switching user', 'error');
            }
        } catch(e) {
            showToast('Network error while switching user', 'error');
        }
    }

    /* ─── REAL-TIME NO-REFRESH ENGINE (SPA + LIVE SYNC) ─── */
    let isSyncing = false;

    // Helper: Check if user is currently entering or editing data
    function isUserDataEntryActive() {
        const path = window.location.pathname.toLowerCase();
        if (path.includes('/create') || path.includes('/edit') || path.includes('/pos')) {
            return true;
        }
        // Check if any modal is open
        const hasOpenModal = !!document.querySelector('.modal-overlay.open, .modal-overlay[style*="display: flex"], .modal-overlay[style*="display: block"], .modal.open');
        if (hasOpenModal) {
            return true;
        }
        // Check if active element is an input
        if (document.activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            return true;
        }
        // Check if any input or textarea on the page has unsaved text
        const hasUnsavedInput = Array.from(document.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), textarea')).some(el => {
            return el.value && el.value.trim() !== '' && el.value !== el.defaultValue;
        });
        if (hasUnsavedInput) {
            return true;
        }
        return false;
    }

    // Trigger manual or post-action sync without page reload
    async function triggerLiveSync(forceSync = false, isSilent = false) {
        if (isSyncing) return;
        if (!forceSync && isUserDataEntryActive()) {
            return; // NEVER sync or touch DOM while user is entering data!
        }

        isSyncing = true;
        const icon = document.getElementById('liveSyncIcon');
        const text = document.getElementById('liveSyncText');
        if (icon) icon.classList.add('sync-spinning');
        if (text && !isSilent) text.textContent = 'Syncing...';

        try {
            const res = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            });
            if (res.ok && (forceSync || !isUserDataEntryActive())) {
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // 1. Update all .card-body containers (Tables, Lists, Empty States) - ONLY if safe
                const newCards = doc.querySelectorAll('.card-body');
                const curCards = document.querySelectorAll('.card-body');
                if (newCards.length && newCards.length === curCards.length) {
                    curCards.forEach((cur, idx) => {
                        // Protect any card containing forms, inputs, textareas, selects
                        if (!forceSync && cur.querySelector('form, input, textarea, select')) {
                            return;
                        }
                        const newCard = newCards[idx];
                        if (cur.innerHTML !== newCard.innerHTML) {
                            cur.innerHTML = newCard.innerHTML;
                        }
                    });
                } else {
                    const newTable = doc.querySelector('.table-wrap');
                    const curTable = document.querySelector('.table-wrap');
                    if (newTable && curTable && (forceSync || !curTable.querySelector('input, form')) && newTable.innerHTML !== curTable.innerHTML) {
                        curTable.innerHTML = newTable.innerHTML;
                    }
                }

                // 2. Update Stats Grid & Metrics if present
                const newStats = doc.querySelector('.stats-grid');
                const curStats = document.querySelector('.stats-grid');
                if (newStats && curStats && newStats.innerHTML !== curStats.innerHTML) {
                    curStats.innerHTML = newStats.innerHTML;
                }

                if (!isSilent) showToast('Data synchronized in real-time!', 'success');
            }
        } catch (e) {
            if (!isSilent) showToast('Sync failed. Please check connection.', 'error');
        } finally {
            isSyncing = false;
            if (icon) icon.classList.remove('sync-spinning');
            if (text) text.textContent = 'Live Sync';
        }
    }

    // Smart sync on window focus / tab switch - ONLY if not entering data
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !isUserDataEntryActive()) {
            triggerLiveSync(false, true);
        }
    });
    window.addEventListener('focus', () => {
        if (!document.hidden && !isUserDataEntryActive()) {
            triggerLiveSync(false, true);
        }
    });

    // Real-time background sync every 15s (ONLY on list/dashboard pages when idle)
    setInterval(() => {
        if (!document.hidden && !isUserDataEntryActive()) {
            triggerLiveSync(false, true);
        }
    }, 15000);

    // SPA Navigation Engine (Smooth load without page refresh)
    async function navigateSpa(url, push = true) {
        const bar = document.getElementById('spaProgressBar');
        if (bar) { bar.style.opacity = '1'; bar.style.width = '35%'; }

        try {
            const res = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            });
            if (bar) bar.style.width = '75%';

            if (res.ok) {
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newContent = doc.querySelector('.content');
                const curContent = document.querySelector('.content');
                if (newContent && curContent) {
                    curContent.innerHTML = newContent.innerHTML;
                    reexecuteScripts(curContent);

                    // Also evaluate any scripts that were rendered in doc body
                    const docScripts = doc.querySelectorAll('script');
                    docScripts.forEach(oldScript => {
                        const code = oldScript.innerHTML;
                        if (code && !code.includes('navigateSpa') && !code.includes('spaProgressBar')) {
                            try {
                                const newScript = document.createElement('script');
                                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                                newScript.appendChild(document.createTextNode(code));
                                document.body.appendChild(newScript);
                                setTimeout(() => newScript.remove(), 150);
                            } catch (e) {
                                console.warn('Script execution warning:', e);
                            }
                        }
                    });
                }

                // Update page title & breadcrumbs
                const newTitle = doc.querySelector('title');
                if (newTitle) document.title = newTitle.innerText;
                const newTopbarTitle = doc.querySelector('.topbar-title');
                const curTopbarTitle = document.querySelector('.topbar-title');
                if (newTopbarTitle && curTopbarTitle) curTopbarTitle.innerHTML = newTopbarTitle.innerHTML;
                const newBreadcrumb = doc.querySelector('.topbar-breadcrumb');
                const curBreadcrumb = document.querySelector('.topbar-breadcrumb');
                if (newBreadcrumb && curBreadcrumb) curBreadcrumb.innerHTML = newBreadcrumb.innerHTML;

                // Update active sidebar nav
                document.querySelectorAll('.sidebar a').forEach(a => {
                    const href = a.getAttribute('href');
                    if (href && (href === url || url.startsWith(href) && href !== '/')) {
                        a.classList.add('active');
                    } else {
                        a.classList.remove('active');
                    }
                });

                if (push) window.history.pushState({ url: url }, '', url);
                window.scrollTo({ top: 0, behavior: 'instant' });

                if (bar) {
                    bar.style.width = '100%';
                    setTimeout(() => { bar.style.opacity = '0'; bar.style.width = '0%'; }, 250);
                }
            } else {
                window.location.href = url;
            }
        } catch (e) {
            window.location.href = url;
        }
    }

    // Helper to execute embedded scripts in swapped content cleanly
    function reexecuteScripts(container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            if (!oldScript.src && oldScript.innerHTML) {
                try {
                    window.eval(oldScript.innerHTML);
                } catch (e) {
                    console.warn('Script execution warning:', e);
                }
            }
        });
    }

    // Intercept clicks for internal links to prevent full page reloads
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:') || link.getAttribute('target') === '_blank') return;
        if (link.closest('#userDropdownMenu') && href.includes('logout')) return;
        if (href.includes('export') || href.includes('download') || href.includes('print')) return;

        // Check if same origin
        try {
            const targetUrl = new URL(href, window.location.origin);
            if (targetUrl.origin === window.location.origin) {
                e.preventDefault();
                navigateSpa(targetUrl.href);
            }
        } catch {}
    });

    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.url) {
            navigateSpa(e.state.url, false);
        } else {
            navigateSpa(window.location.href, false);
        }
    });
</script>
@yield('scripts')
@stack('scripts')
</body>
</html>