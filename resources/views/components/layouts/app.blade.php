<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SchoolLMS' }} — Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@300;400;500;600;700&family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <x-theme-script />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Wrapped in @layer base on purpose. Unlayered, this reset beat every
           rule in app.css's @layer components — an unlayered rule always wins
           over a layered one — which silently stripped the padding, margin and
           border off .card, .btn, .filter-card, .empty-state and the shared
           form controls. Inside `base` it still resets the browser defaults
           while losing to the component layer, which is what it was for. */
        @layer base {
            * { box-sizing: border-box; margin: 0; padding: 0; }
        }

        body {
            font-family: var(--font-body);
            background: var(--surface-2);
            color: var(--text-primary);
            /* dvh tracks the mobile URL bar as it collapses; the vh line is the
               fallback for browsers without dvh support. */
            min-height: 100vh;
            min-height: 100dvh;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3 { font-family: var(--font-display); }

        /* Arabic glyphs fall through to Cairo per-glyph via the font stacks above;
           force Cairo for headings in RTL so the Latin-only display serif never shows. */
        [dir="rtl"] h1, [dir="rtl"] h2, [dir="rtl"] h3 {
            font-family: var(--font-display-rtl);
        }

        /* === LAYOUT === */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* === SIDEBAR === */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            min-height: 100vh;
            min-height: 100dvh;
            max-height: 100dvh;
            position: fixed;
            top: 0;
            inset-inline-start: 0;
            z-index: 50;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 20% 20%, color-mix(in srgb, var(--primary-light) 20%, transparent) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 80%, color-mix(in srgb, var(--primary) 15%, transparent) 0%, transparent 60%);
            pointer-events: none;
        }

        .sidebar-logo {
            padding: 24px 20px 20px;
            border-bottom: 1px solid color-mix(in srgb, var(--primary-light) 20%, transparent);
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
        }

        .logo-mark {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px color-mix(in srgb, var(--primary) 40%, transparent);
        }

        .logo-mark svg { width: 20px; height: 20px; color: white; }

        .logo-text {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 700;
            color: white;
            letter-spacing: -0.3px;
        }

        .logo-sub {
            font-size: 10px;
            color: var(--sidebar-text);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-weight: 500;
        }

        .sidebar-nav {
            padding: 16px 12px;
            flex: 1;
            /* A flex item won't shrink below its content without this, so on a
               short screen the nav would push the user footer out of the
               clipped sidebar instead of scrolling. */
            min-height: 0;
            overflow-y: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            position: relative;
            z-index: 1;
        }

        .sidebar-nav::-webkit-scrollbar { display: none; }

        .nav-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: color-mix(in srgb, var(--sidebar-text) 40%, transparent);
            padding: 12px 8px 6px;
            margin-top: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 2px;
            position: relative;
        }

        .nav-item:hover {
            /* Sits on the translucent hover wash over the fixed-dark sidebar,
               not on a brand fill — stays white in both themes. */
            background: var(--sidebar-hover);
            color: white;
        }

        .nav-item.active {
            background: var(--primary);
            color: var(--on-primary);
            box-shadow: 0 4px 12px color-mix(in srgb, var(--primary) 40%, transparent);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            inset-inline-start: -12px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: var(--primary-light);
            border-radius: 0 4px 4px 0;
        }

        [dir="rtl"] .nav-item.active::before {
            border-radius: 4px 0 0 4px;
        }

        .nav-item svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            opacity: 0.8;
        }

        .nav-item.active svg { opacity: 1; }
        .nav-group { margin-bottom: 2px; }
        .nav-group summary { list-style: none; cursor: pointer; }
        .nav-group summary::-webkit-details-marker { display: none; }
        .nav-group summary::after { content: '⌄'; margin-inline-start: auto; font-size: 13px; opacity: 0.65; transition: transform 0.2s; }
        .nav-group[open] summary::after { transform: rotate(180deg); }
        .nav-submenu { padding: 2px 0 4px 28px; }
        [dir="rtl"] .nav-submenu { padding: 2px 28px 4px 0; }
        .nav-subitem { display: block; padding: 7px 12px; border-radius: 8px; color: var(--sidebar-text); text-decoration: none; font-size: 12.5px; }
        .nav-subitem:hover, .nav-subitem.active { background: var(--sidebar-hover); color: white; }

        .sidebar-footer {
            padding: 16px 12px 20px;
            border-top: 1px solid color-mix(in srgb, var(--primary-light) 20%, transparent);
            position: relative;
            z-index: 1;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(255,255,255,0.05);
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: var(--on-primary);
            flex-shrink: 0;
        }

        .user-info { flex: 1; min-width: 0; }
        .user-name {
            font-size: 13px;
            font-weight: 600;
            color: white;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-role {
            font-size: 11px;
            color: var(--sidebar-text);
            text-transform: capitalize;
        }

        /* === MAIN CONTENT === */
        .main-content {
            margin-inline-start: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* === TOPBAR === */
        .topbar {
            height: var(--topbar-height);
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 40;
            gap: 16px;
        }

        .topbar-left { display: flex; align-items: center; gap: 16px; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }

        .page-title {
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .breadcrumb {
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .topbar-user-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 14px 6px 6px;
            border-radius: 50px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            font-size: 13px;
            font-weight: 500;
        }

        .topbar-user-btn:hover { border-color: var(--primary); }

        .topbar-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--on-primary);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 8px;
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-family: var(--font-body);
        }

        .logout-btn:hover {
            background: var(--danger-tint);
            border-color: var(--danger-border);
            color: var(--danger-dark);
        }

        .logout-btn svg { width: 15px; height: 15px; }

        .notification-menu { position: relative; }
        .notification-trigger {
            position: relative; width: 36px; height: 36px; display: flex; align-items: center;
            justify-content: center; border: 1px solid var(--border); border-radius: 9px;
            background: var(--surface); color: var(--text-secondary); cursor: pointer;
            list-style: none;
        }
        .notification-trigger::-webkit-details-marker { display: none; }
        .notification-trigger svg { width: 17px; height: 17px; }
        .notification-trigger:hover { color: var(--primary-dark); border-color: var(--primary-light); }
        .notification-count {
            position: absolute; top: -5px; inset-inline-end: -5px; min-width: 17px; height: 17px;
            display: flex; align-items: center; justify-content: center; padding: 0 4px;
            border-radius: 9px; background: var(--danger); color: white; font-size: 10px; font-weight: 700;
        }
        .notification-dropdown {
            position: absolute; top: calc(100% + 10px); inset-inline-end: 0; width: min(360px, calc(100vw - 32px));
            z-index: 60; background: var(--surface); border: 1px solid var(--border-soft);
            border-radius: 13px; box-shadow: var(--shadow-card); overflow: hidden;
        }
        .notification-dropdown-header {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 14px 16px; border-bottom: 1px solid var(--border-soft);
        }
        .notification-dropdown-title { font-size: 13px; font-weight: 700; color: var(--text-primary); }
        .notification-dropdown-link { color: var(--primary-dark); font-size: 11px; font-weight: 600; text-decoration: none; }
        .notification-dropdown-item {
            display: flex; align-items: flex-start; gap: 10px; width: 100%; padding: 12px 16px;
            border: 0; border-bottom: 1px solid var(--border-soft); background: transparent;
            color: var(--text-primary); text-align: start; cursor: pointer; font-family: var(--font-body);
        }
        .notification-dropdown-item:hover { background: var(--surface-2); }
        .notification-dropdown-item.unread { background: var(--primary-tint); }
        .notification-dropdown-item strong { display: block; font-size: 12px; }
        .notification-dropdown-item span { display: block; margin-top: 3px; color: var(--text-muted); font-size: 11px; line-height: 1.4; }
        .notification-dropdown-empty { padding: 24px 16px; color: var(--text-muted); font-size: 12px; text-align: center; }

        /* === CONTENT AREA === */
        .content-area {
            padding: 28px;
            flex: 1;
        }

        /* === FLASH MESSAGES === */
        .flash-success, .flash-error {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease;
        }

        .flash-success {
            background: var(--success-tint);
            border: 1px solid var(--success-border);
            color: var(--success-text);
        }

        .flash-error {
            background: var(--danger-tint);
            border: 1px solid var(--danger-border);
            color: var(--danger-text);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* === MOBILE TOGGLE === */
        .mobile-toggle {
            display: none;
            background: none;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 6px;
            cursor: pointer;
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .mobile-toggle { display: flex; align-items: center; justify-content: center; }
            .sidebar { transform: translateX(-100%); }
            [dir="rtl"] .sidebar { transform: translateX(100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-inline-start: 0; }
            .content-area { padding: 16px; }

            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: var(--overlay);
                z-index: 49;
            }
            .sidebar-overlay.open { display: block; }

            /* The drawer is the only way to navigate on mobile, so it must not
               exceed the viewport on small phones. */
            .sidebar { width: min(var(--sidebar-width), 82vw); }

            .topbar { padding: 0 16px; gap: 10px; }
            .topbar-left, .topbar-right { gap: 10px; }
            .page-title { font-size: 17px; }

            /* The name already shows in the sidebar footer; dropping it here
               keeps the logout button on-screen at 360px. */
            .topbar-username { display: none; }
        }

        /* Below 380px the logout label pushes the row over; keep the icon. */
        @media (max-width: 380px) {
            .logout-btn { padding: 7px 10px; }
            .logout-btn span { display: none; }
        }
    </style>
</head>
<body>
@php
    $user = auth()->user();
    $currentRole = session('impersonate_role', $user->role->value);
    $currentRole = is_string($currentRole) ? $currentRole : $currentRole->value;
    $impersonating = $user->role->value === 'admin' && session()->has('impersonate_role');
    $impersonationExpiresAt = $impersonating && is_numeric(session('impersonate_expires_at'))
        ? \Illuminate\Support\Carbon::createFromTimestamp((int) session('impersonate_expires_at'))
        : null;
    $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper($w[0]))->take(2)->join('');
    $unreadNotificationCount = $user->unreadNotifications()->count();
    $recentNotifications = $user->notifications()->latest()->limit(5)->get();

    $allMenuItems = [
        'admin' => [
            ['label' => 'Dashboard',     'route' => 'dashboard',                  'icon' => 'home'],
            ['label' => 'Users',         'icon' => 'users', 'children' => [
                ['label' => 'All Users',         'route' => 'admin.users.index'],
                ['label' => 'Administrators',     'route' => 'admin.users.index', 'query' => ['role' => 'admin']],
                ['label' => 'Teachers',           'route' => 'admin.users.index', 'query' => ['role' => 'teacher']],
                ['label' => 'Students',           'route' => 'admin.users.index', 'query' => ['role' => 'student']],
                ['label' => 'Parents',            'route' => 'admin.users.index', 'query' => ['role' => 'parent']],
            ]],
            ['label' => 'Complaints',    'route' => 'admin.complaints.index',      'icon' => 'check-circle'],
            ['label' => 'Schools',       'route' => 'admin.schools.index',          'icon' => 'book'],
            ['label' => 'Academic Year', 'route' => 'admin.academic-years.index', 'icon' => 'calendar'],
            ['label' => 'Classrooms',    'route' => 'classrooms.index',           'icon' => 'book'],
            ['label' => 'Grades',        'route' => 'admin.grades.index',           'icon' => 'book'],
            ['label' => 'Subjects',      'route' => 'admin.subjects.index',       'icon' => 'book'],
            ['label' => 'Assignments',   'route' => 'admin.assignments.index',    'icon' => 'star'],
            ['label' => 'Calendar',      'route' => 'admin.calendar.index',       'icon' => 'calendar'],
            ['label' => 'Schedule',      'route' => 'admin.schedule.index',       'icon' => 'calendar'],
            ['label' => 'Exam Types',    'route' => 'admin.exam-types.index',        'icon' => 'star'],
            ['label' => 'Test Builder',  'route' => 'admin.diagnostic.test-builder', 'icon' => 'bar-chart'],
            ['label' => 'Knowledge Map', 'route' => 'admin.diagnostic.knowledge-map','icon' => 'bar-chart'],
            ['label' => 'Settings',      'route' => 'settings.index',                'icon' => 'settings'],
            ['label' => 'Wallet',        'route' => 'admin.wallet.index',            'icon' => 'credit-card'],
            ['label' => 'Reports',       'route' => 'admin.reports.index',         'icon' => 'bar-chart'],
        ],
        'teacher' => [
            ['label' => 'Dashboard',        'route' => 'dashboard',                'icon' => 'home'],
            ['label' => 'My Schedule',       'route' => 'teacher.schedule',         'icon' => 'calendar'],
            ['label' => 'Classrooms',        'route' => 'classrooms.index',         'icon' => 'book'],
            ['label' => 'Homework',          'route' => 'teacher.homework',           'icon' => 'book'],
            ['label' => 'Complaints',        'route' => 'complaints.index',            'icon' => 'check-circle'],
            ['label' => 'Attendance',        'route' => 'teacher.attendance',       'icon' => 'check-circle'],
            ['label' => 'Justifications',    'route' => 'teacher.justifications',   'icon' => 'check-circle'],
            ['label' => 'Behavioral Notes',  'route' => 'teacher.behavioral-notes', 'icon' => 'bar-chart'],
            ['label' => 'Grade Entry',       'route' => 'teacher.grades.entry',          'icon' => 'star'],
            ['label' => 'Knowledge Map',     'route' => 'teacher.diagnostic.knowledge-map','icon' => 'bar-chart'],
            ['label' => 'Salaries',          'route' => 'teacher.salaries',              'icon' => 'credit-card'],
            ['label' => 'Settings',          'route' => 'settings.index',                'icon' => 'settings'],
        ],
        'student' => [
            ['label' => 'Dashboard',  'route' => 'dashboard',          'icon' => 'home'],
            ['label' => 'My Schedule','route' => 'student.schedule',   'icon' => 'calendar'],
            ['label' => 'Homework',   'route' => 'student.homework',    'icon' => 'book'],
            ['label' => 'Complaints', 'route' => 'complaints.index',      'icon' => 'check-circle'],
            ['label' => 'My Results',    'route' => 'student.results',              'icon' => 'star'],
            ['label' => 'Diagnostic',    'route' => 'student.diagnostic.test',      'icon' => 'bar-chart'],
            ['label' => 'Knowledge Map', 'route' => 'student.diagnostic.knowledge-map','icon' => 'bar-chart'],
            ['label' => 'Attendance',    'route' => 'student.attendance',            'icon' => 'check-circle'],
            ['label' => 'Settings',      'route' => 'settings.index',                'icon' => 'settings'],
        ],
        'parent' => [
            ['label' => 'Dashboard',       'route' => 'dashboard',               'icon' => 'home'],
            ['label' => 'My Children',      'route' => 'parent.children',         'icon' => 'users'],
            ['label' => 'Results',          'route' => 'parent.results',          'icon' => 'star'],
            ['label' => 'Attendance',       'route' => 'parent.attendance',       'icon' => 'check-circle'],
            ['label' => 'Behavioral Notes', 'route' => 'parent.behavioral-notes', 'icon' => 'bar-chart'],
            ['label' => 'Complaints',      'route' => 'complaints.index',             'icon' => 'check-circle'],
            ['label' => 'Payments',         'route' => 'parent.payments.index',   'icon' => 'credit-card'],
            ['label' => 'Settings',         'route' => 'settings.index',          'icon' => 'settings'],
        ],
    ];

    $menuItems = $allMenuItems[$currentRole] ?? $allMenuItems['student'];

    $icons = [
        'home' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        'users' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
        'book' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
        'check-circle' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'star' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>',
        'calendar' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        'settings' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>',
        'bar-chart' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
        'credit-card' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
        'truck' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2h10l2-2zM13 6l3 4h4l-1 6h-2"/></svg>',
    ];
@endphp

<div class="app-wrapper">
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay" data-sidebar-close></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-mark">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21a12.083 12.083 0 01-6.16-10.422L12 14z"/>
                </svg>
            </div>
            <div>
                <div class="logo-text">SchoolLMS</div>
                <div class="logo-sub">{{ __("Management System") }}</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">{{ __("Navigation") }}</div>
            @foreach($menuItems as $item)
                @if(isset($item['children']))
                    @php
                        $hasActiveChild = collect($item['children'])->contains(function (array $child): bool {
                            return request()->routeIs($child['route'])
                                && request()->query('role') === ($child['query']['role'] ?? null);
                        });
                    @endphp
                    <details class="nav-group" @if($hasActiveChild) open @endif>
                        <summary class="nav-item {{ $hasActiveChild ? 'active' : '' }}">
                            {!! $icons[$item['icon']] ?? $icons['home'] !!}
                            <span>{{ __($item['label']) }}</span>
                        </summary>
                        <div class="nav-submenu">
                            @foreach($item['children'] as $child)
                                @php
                                    $childQuery = $child['query'] ?? [];
                                    $childActive = request()->routeIs($child['route'])
                                        && request()->query('role') === ($childQuery['role'] ?? null);
                                @endphp
                                <a href="{{ route($child['route'], $childQuery) }}" class="nav-subitem {{ $childActive ? 'active' : '' }}">{{ __($child['label']) }}</a>
                            @endforeach
                        </div>
                    </details>
                @else
                    @php
                        $isActive = request()->routeIs($item['route']) || (isset($item['active']) && $item['active']);
                        $routeExists = \Illuminate\Support\Facades\Route::has($item['route']);
                        $href = $routeExists ? route($item['route']) : '#';
                    @endphp
                    <a href="{{ $href }}" class="nav-item {{ $isActive ? 'active' : '' }}">
                        {!! $icons[$item['icon']] ?? $icons['home'] !!}
                        <span>{{ __($item['label']) }}</span>
                    </a>
                @endif
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">{{ $initials }}</div>
                <div class="user-info">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-role">{{ __(ucfirst($currentRole)) }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="mobile-toggle" data-sidebar-toggle aria-label="Toggle sidebar">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                    <div class="page-title">{{ $pageTitle ?? __('Dashboard') }}</div>
                </div>
            </div>

            <div class="topbar-right">
                <details class="notification-menu">
                    <summary class="notification-trigger" aria-label="{{ __('Notifications') }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if($unreadNotificationCount > 0)
                            <span class="notification-count">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                        @endif
                    </summary>
                    <div class="notification-dropdown">
                        <div class="notification-dropdown-header">
                            <span class="notification-dropdown-title">{{ __('Notifications') }}</span>
                            <a class="notification-dropdown-link" href="{{ route('notifications.index') }}">{{ __('View all') }}</a>
                        </div>
                        @forelse($recentNotifications as $notification)
                            @php
                                $notificationData = $notification->data;
                                $notificationTitle = __($notificationData['title_key'] ?? $notificationData['title'] ?? 'Notification');
                                $notificationMessage = __($notificationData['message_key'] ?? $notificationData['message'] ?? '', $notificationData['message_data'] ?? []);
                            @endphp
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                <button type="submit" class="notification-dropdown-item {{ $notification->read_at ? '' : 'unread' }}">
                                    <span>
                                        <strong>{{ $notificationTitle }}</strong>
                                        <span>{{ $notificationMessage }}</span>
                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    </span>
                                </button>
                            </form>
                        @empty
                            <div class="notification-dropdown-empty">{{ __('You have no notifications yet.') }}</div>
                        @endforelse
                    </div>
                </details>

                {{-- Language and theme controls now live on the shared settings
                     page (route: settings.index), linked from every role's sidebar. --}}
                <span class="topbar-username" style="font-size: 13px; color: var(--text-secondary); font-weight: 500;">{{ $user->name }}</span>

                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn" aria-label="{{ __('Logout') }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>{{ __("Logout") }}</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Content -->
        <main class="content-area">
            @if($impersonating)
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; background:var(--warning-tint); border:1px solid var(--warning-border); color:var(--warning-text); padding:12px 16px; border-radius:10px; margin-bottom:16px; font-size:13px;">
                    <span>{{ __('Read-only impersonation: :role. Expires at :time.', ['role' => __(ucfirst($currentRole)), 'time' => $impersonationExpiresAt?->format('H:i')]) }}</span>
                    <form action="{{ route('admin.impersonate.stop') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" style="padding:6px 12px; border:1px solid var(--warning-dark); border-radius:7px; background:var(--surface); color:var(--warning-text); font-size:12px; font-weight:600; cursor:pointer;">{{ __('Stop impersonation') }}</button>
                    </form>
                </div>
            @endif

            @if(session('success'))
                <x-alert type="success">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </x-alert>
            @endif

            @if(session('error') || $errors->any())
                <x-alert type="error">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') ?? $errors->first() }}
                </x-alert>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

</body>
</html>
