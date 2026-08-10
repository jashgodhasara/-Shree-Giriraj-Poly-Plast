<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; overflow-x: hidden; }

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
        .main { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        .topbar {
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226,232,240,.8);
            padding: 0 28px;
            height: 64px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 0 rgba(0,0,0,.04);
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

        .content { padding: 28px; flex: 1; }

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

        /* ── SIDEBAR OVERLAY ── */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,.6); backdrop-filter: blur(3px);
            z-index: 199;
        }
        .sidebar-overlay.open { display: block; }

        /* ── RESPONSIVE BREAKPOINTS ── */
        @media (max-width: 768px) {
            body { display: block; }

            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                z-index: 200;
                box-shadow: 4px 0 24px rgba(0,0,0,.3);
            }
            .sidebar.open { transform: translateX(0); }

            .main { margin-left: 0; }

            .topbar {
                padding: 0 16px;
                height: 56px;
            }
            .topbar-breadcrumb { display: none; }
            .topbar-date { display: none; }
            .topbar-company { display: none; }
            .hamburger { display: flex; }
            .topbar-title { font-size: 15px; }

            .content { padding: 16px; }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
                margin-bottom: 20px;
            }
            .stat-value { font-size: 20px; }

            .form-row.cols-2,
            .form-row.cols-3 { grid-template-columns: 1fr; }

            .card-header { padding: 12px 16px; flex-wrap: wrap; gap: 8px; }
            .card-body { padding: 16px; }

            table { font-size: 12px; }
            th, td { padding: 9px 10px; }

            .modal { max-width: 100%; border-radius: 12px 12px 0 0; }
            .modal-overlay { align-items: flex-end; padding: 0; }

            #toast-container { bottom: 16px; right: 12px; left: 12px; }
            .toast { max-width: 100%; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .stat-card { padding: 14px 16px; }
            .stat-value { font-size: 18px; }
            .stat-icon { width: 38px; height: 38px; font-size: 16px; }

            .btn { padding: 8px 12px; font-size: 12px; }
            .btn-sm { padding: 5px 10px; font-size: 11px; }

            .card-header h3 { font-size: 13px; }
        }
    </style>
</head>
<body>

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
    <div class="sidebar-section">Sales Module</div>
    <a href="{{ route('invoices.create') }}" class="{{ request()->routeIs('invoices.create') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-file-signature"></i></span>
        <span class="nav-label">Sales Order</span>
    </a>
    <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.index') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-truck-ramp-box"></i></span>
        <span class="nav-label">Delivery Notes</span>
    </a>
    <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.index') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-file-invoice-dollar"></i></span>
        <span class="nav-label">Sales</span>
    </a>
    <a href="{{ route('ledger.index') }}" class="{{ request()->routeIs('ledger.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-file-circle-minus"></i></span>
        <span class="nav-label">Credit Note</span>
    </a>
    <a href="{{ route('material-transactions.index') }}" class="{{ request()->routeIs('material-transactions.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-rotate-left"></i></span>
        <span class="nav-label">Rejection In</span>
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Purchase Module</div>
    <a href="{{ route('purchase-orders.index') }}" class="{{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-cart-flatbed"></i></span>
        <span class="nav-label">Purchase Order</span>
    </a>
    <a href="{{ route('purchase-orders.create') }}" class="{{ request()->routeIs('purchase-orders.create') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-boxes-packing"></i></span>
        <span class="nav-label">Receipt Note</span>
    </a>
    <a href="{{ route('purchase-orders.index') }}" class="{{ request()->routeIs('purchase-orders.index') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-bag-shopping"></i></span>
        <span class="nav-label">Purchase</span>
    </a>
    <a href="{{ route('ledger.index') }}" class="{{ request()->routeIs('ledger.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-file-circle-plus"></i></span>
        <span class="nav-label">Debit Note</span>
    </a>
    <a href="{{ route('material-transactions.index') }}" class="{{ request()->routeIs('material-transactions.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-rotate-right"></i></span>
        <span class="nav-label">Rejection Out</span>
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Inventory &amp; Production</div>
    <a href="{{ route('materials.index') }}" class="{{ request()->routeIs('materials.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-warehouse"></i></span>
        <span class="nav-label">Inventory</span>
    </a>
    <a href="{{ route('material-transactions.index') }}" class="{{ request()->routeIs('material-transactions.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-circle-arrow-down"></i></span>
        <span class="nav-label">Material In</span>
    </a>
    <a href="{{ route('material-transactions.index') }}" class="{{ request()->routeIs('material-transactions.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-circle-arrow-up"></i></span>
        <span class="nav-label">Material Out</span>
    </a>
    <a href="{{ route('production.index') }}" class="{{ request()->routeIs('production.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-book-bookmark"></i></span>
        <span class="nav-label">Stock Journal</span>
    </a>
    <a href="{{ route('production.index') }}" class="{{ request()->routeIs('production.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-sitemap"></i></span>
        <span class="nav-label">BOM</span>
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Job Work</div>
    <a href="{{ route('jobworks.index') }}" class="{{ request()->routeIs('jobworks.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-right-to-bracket"></i></span>
        <span class="nav-label">Job Work In</span>
    </a>
    <a href="{{ route('jobworks.index') }}" class="{{ request()->routeIs('jobworks.*') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa fa-right-from-bracket"></i></span>
        <span class="nav-label">Job Work Out</span>
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
            <a href="{{ route('onboard.index') }}" class="btn btn-outline btn-sm" style="font-size:11px; border-color:var(--primary); color:var(--primary);">
                <i class="fa fa-wand-magic-sparkles"></i> AI Setup Configurator
            </a>
            <div class="topbar-date">
                <i class="fa fa-calendar" style="color:var(--primary)"></i>
                <span id="topbar-date-text"></span>
            </div>
            <a href="{{ route('branches.index') }}" class="topbar-company" style="text-decoration:none;" title="Click to manage multi-location branches">
                <i class="fa fa-location-dot"></i> {{ session('current_branch', 'Ahmedabad, Gujarat') }}
            </a>
        </div>
    </div>
    <div class="content">
        @yield('content')
    </div>
</div>

<div id="toast-container"></div>

<script>
    // Live date
    (function() {
        const el = document.getElementById('topbar-date-text');
        if (el) {
            const d = new Date();
            el.textContent = d.toLocaleDateString('en-IN', { weekday:'short', day:'2-digit', month:'short', year:'numeric' });
        }
    })();

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

    /* ── AJAX Form Submit ── */
    function submitForm(formEl, url, method, onSuccess) {
        const btn = formEl.querySelector('[type=submit]');
        const origText = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...'; }

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
            if (btn) { btn.disabled = false; btn.innerHTML = origText; }
            if (res.success) {
                showToast(res.message, 'success');
                if (onSuccess) onSuccess(res);
                else location.reload();
            } else {
                showToast(res.message || 'An error occurred.', 'error');
            }
        })
        .catch(() => {
            if (btn) { btn.disabled = false; btn.innerHTML = origText; }
            showToast('Network error. Please try again.', 'error');
        });
    }

    /* ── Delete Record ── */
    function deleteRecord(url, label) {
        if (!confirm(`Delete this ${label}?\n\nThis action cannot be undone.`)) return;
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) { showToast(res.message, 'success'); location.reload(); }
            else showToast(res.message, 'error');
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
</script>
@yield('scripts')
</body>
</html>
