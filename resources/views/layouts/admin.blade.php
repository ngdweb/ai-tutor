<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — NGD Technolab</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #ede9fe;
            --primary-text: #4f46e5;
            --bg: #f1f5f9;
            --surface: #ffffff;
            --surface2: #f8fafc;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-muted: #64748b;
            --sidebar-w: 256px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-logo {
            padding: 18px 18px;
            display: flex;
            align-items: center;
            gap: 0;
            border-bottom: 1px solid var(--border);
        }
        .ngd-logo-wrap { display: flex; flex-direction: column; align-items: flex-start; }
        .ngd-letters {
            display: flex; align-items: baseline; gap: 1px;
            line-height: 1;
        }
        .ngd-n { font-size: 30px; font-weight: 900; color: #099CEB; letter-spacing: -1px; font-family: 'Inter', sans-serif; }
        .ngd-g { font-size: 30px; font-weight: 900; color: #F03728; letter-spacing: -1px; font-family: 'Inter', sans-serif; }
        .ngd-d { font-size: 30px; font-weight: 900; color: #FFB300; letter-spacing: -1px; font-family: 'Inter', sans-serif; }
        .ngd-sub {
            font-size: 9.5px; font-weight: 600;
            letter-spacing: 3.5px; text-transform: uppercase;
            color: #00B0AA; margin-top: 2px; margin-left: 2px;
        }

        .sidebar-nav { padding: 14px 10px; flex: 1; overflow-y: auto; }
        .nav-section-title {
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1.2px;
            color: var(--text-muted);
            padding: 6px 10px 8px;
            margin-top: 4px;
        }

        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px; font-weight: 500;
            margin-bottom: 2px;
            transition: all 0.18s ease;
        }
        .nav-item:hover { background: var(--surface2); color: var(--text); }
        .nav-item.active {
            background: var(--primary-light);
            color: var(--primary-text);
            font-weight: 600;
        }
        .nav-icon svg { width: 17px; height: 17px; flex-shrink: 0; }

        .sidebar-footer {
            padding: 14px 10px;
            border-top: 1px solid var(--border);
        }
        .user-card {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: var(--surface2);
            border: 1px solid var(--border);
        }
        .user-avatar {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: #fff;
            flex-shrink: 0;
        }
        .user-name { font-size: 13px; font-weight: 600; color: var(--text); }
        .user-email { font-size: 11px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
        .logout-btn {
            margin-left: auto; background: none; border: none;
            cursor: pointer; color: var(--text-muted); padding: 5px;
            border-radius: 7px; display: flex; transition: all 0.18s;
        }
        .logout-btn:hover { background: #fee2e2; color: #ef4444; }
        .logout-btn svg { width: 16px; height: 16px; }

        /* ── Main ── */
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Topbar ── */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 28px; height: 62px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .menu-btn {
            display: none; background: none; border: none;
            color: var(--text-muted); cursor: pointer; padding: 6px; border-radius: 8px;
        }
        .menu-btn:hover { background: var(--surface2); }
        .menu-btn svg { width: 21px; height: 21px; }
        .page-title { font-size: 17px; font-weight: 700; color: var(--text); }

        .topbar-right { display: flex; align-items: center; gap: 10px; }

        /* ── Content ── */
        .content { padding: 26px 28px; flex: 1; }

        /* ── Overlay ── */
        .overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,0.35); z-index: 90;
        }

        /* scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); box-shadow: 4px 0 24px rgba(0,0,0,0.12); }
            .main { margin-left: 0; }
            .menu-btn { display: flex; }
            .overlay.visible { display: block; }
            .content { padding: 20px 16px; }
            .topbar { padding: 0 16px; }
            .topbar-date { display: none; }
        }
    </style>
    @yield('styles')
</head>
<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="ngd-logo-wrap">
            <div class="ngd-letters">
                <span class="ngd-n">N</span><span class="ngd-g">G</span><span class="ngd-d">D</span>
            </div>
            <div class="ngd-sub">Technolab</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Navigation</div>

        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>
            </span>
            Dashboard
        </a>

        <div class="nav-section-title" style="margin-top:14px;">Modules</div>

        <a href="{{ route('video-learning.index') }}" class="nav-item {{ request()->routeIs('video-learning.*') ? 'active' : '' }}">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="23 7 16 12 23 17 23 7"/>
                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                </svg>
            </span>
            Video Learning with Word
        </a>

        <a href="{{ route('api-list.index') }}" class="nav-item {{ request()->routeIs('api-list.*') ? 'active' : '' }}">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="16 18 22 12 16 6"/>
                    <polyline points="8 6 2 12 8 18"/>
                </svg>
            </span>
            API List
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
            <div style="min-width:0;">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-email">{{ auth()->user()->email }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn" title="Logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-btn" onclick="toggleSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <span class="page-title">@yield('page-title', 'Dashboard')</span>
        </div>
        <div class="topbar-right"></div>
    </header>

    <main class="content">
        @yield('content')
    </main>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('visible');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('visible');
}
</script>
@yield('scripts')
</body>
</html>
