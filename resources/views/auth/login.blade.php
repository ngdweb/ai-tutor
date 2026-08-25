<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — NGD Technolab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f1f5f9;
            position: relative;
            overflow: hidden;
        }

        /* Left decorative panel */
        .left-panel {
            width: 45%;
            background: linear-gradient(145deg, #4f46e5 0%, #7c3aed 60%, #6d28d9 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            top: -160px; right: -160px;
        }
        .left-panel::after {
            content: '';
            position: absolute;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            bottom: -100px; left: -80px;
        }
        .lp-content { position: relative; z-index: 2; text-align: center; }
        .lp-logo-box {
            background: rgba(255,255,255,0.92);
            border-radius: 20px;
            padding: 18px 28px 14px;
            display: inline-block;
            margin-bottom: 28px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
        }
        .lp-ngd-letters {
            display: flex; align-items: baseline; gap: 2px; line-height: 1;
        }
        .lp-n { font-size: 54px; font-weight: 900; color: #099CEB; letter-spacing: -2px; font-family: 'Inter', sans-serif; }
        .lp-g { font-size: 54px; font-weight: 900; color: #F03728; letter-spacing: -2px; font-family: 'Inter', sans-serif; }
        .lp-d { font-size: 54px; font-weight: 900; color: #FFB300; letter-spacing: -2px; font-family: 'Inter', sans-serif; }
        .lp-technolab {
            font-size: 11px; font-weight: 700;
            letter-spacing: 5px; text-transform: uppercase;
            color: #00B0AA; margin-top: 4px; text-align: center;
        }
        .lp-title { font-size: 24px; font-weight: 700; color: #fff; letter-spacing: -0.4px; margin-bottom: 10px; }
        .lp-sub { font-size: 14.5px; color: rgba(255,255,255,0.7); line-height: 1.6; max-width: 300px; }

        .lp-features { margin-top: 40px; display: flex; flex-direction: column; gap: 14px; text-align: left; }
        .lp-feat {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px; padding: 12px 16px;
        }
        .lp-feat-icon {
            width: 32px; height: 32px;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .lp-feat-icon svg { width: 15px; height: 15px; stroke: #fff; fill: none; stroke-width: 2; }
        .lp-feat-text { font-size: 13px; color: rgba(255,255,255,0.85); font-weight: 500; }

        /* Right login panel */
        .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
        }

        .login-header { margin-bottom: 32px; }
        .login-title { font-size: 26px; font-weight: 700; color: #1e293b; letter-spacing: -0.4px; }
        .login-sub { font-size: 14px; color: #64748b; margin-top: 6px; }

        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 7px;
        }

        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; pointer-events: none;
        }
        .input-icon svg { width: 17px; height: 17px; }

        input[type="email"], input[type="password"] {
            width: 100%;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 13px 12px 42px;
            font-size: 14px; color: #1e293b;
            font-family: inherit;
            transition: all 0.2s;
            outline: none;
        }
        input[type="email"]:focus, input[type="password"]:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
        }
        input::placeholder { color: #cbd5e1; }

        .toggle-pw {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #94a3b8; cursor: pointer;
            display: flex; align-items: center; padding: 2px;
            transition: color 0.2s;
        }
        .toggle-pw:hover { color: #4f46e5; }
        .toggle-pw svg { width: 17px; height: 17px; }

        .error-box {
            background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 10px; padding: 11px 14px;
            color: #dc2626; font-size: 13px; margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }
        .error-box::before { content: '⚠'; font-size: 14px; }

        .row-remember {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 22px;
        }
        .row-remember input[type="checkbox"] {
            width: 16px; height: 16px; accent-color: #4f46e5;
            cursor: pointer; padding: 0;
        }
        .row-remember label { margin: 0; font-size: 13.5px; color: #64748b; cursor: pointer; font-weight: 400; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            border: none; border-radius: 11px;
            color: #fff; font-size: 15px; font-weight: 600;
            font-family: inherit; cursor: pointer;
            box-shadow: 0 4px 16px rgba(79,70,229,0.3);
            transition: all 0.2s;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 6px 22px rgba(79,70,229,0.4); }
        .btn-login:active { transform: translateY(0); }

        .login-footer {
            text-align: center; margin-top: 24px;
            font-size: 12px; color: #94a3b8;
        }
        .login-footer span { color: #4f46e5; font-weight: 600; }

        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { background: #fff; min-height: 100vh; padding: 40px 24px; }
        }
    </style>
</head>
<body>

<!-- Left decorative panel -->
<div class="left-panel">
    <div class="lp-content">
        <div class="lp-logo-box">
            <div class="lp-ngd-letters">
                <span class="lp-n">N</span><span class="lp-g">G</span><span class="lp-d">D</span>
            </div>
            <div class="lp-technolab">Technolab</div>
        </div>
        <div class="lp-title">Welcome to NGD Technolab</div>
        <div class="lp-sub">A clean, lightweight admin dashboard for managing your application.</div>

        <div class="lp-features">
            <div class="lp-feat">
                <div class="lp-feat-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="lp-feat-text">Secure bcrypt password hashing</div>
            </div>
            <div class="lp-feat">
                <div class="lp-feat-icon">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                </div>
                <div class="lp-feat-text">Beautiful responsive dashboard</div>
            </div>
            <div class="lp-feat">
                <div class="lp-feat-icon">
                    <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                </div>
                <div class="lp-feat-text">SQLite — zero database config</div>
            </div>
        </div>
    </div>
</div>

<!-- Right login panel -->
<div class="right-panel">
    <div class="login-box">
        <div class="login-header">
            <div class="login-title">Welcome back 👋</div>
            <div class="login-sub">Sign in to your admin account to continue.</div>
        </div>

        @if ($errors->any())
        <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </span>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           placeholder="admin@gmail.com"
                           autocomplete="email" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           autocomplete="current-password" required>
                    <button type="button" class="toggle-pw" onclick="togglePw(this)">
                        <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="row-remember">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Keep me signed in</label>
            </div>

            <button type="submit" class="btn-login">Sign In to Dashboard</button>
        </form>

        <div class="login-footer">
            &copy; {{ date('Y') }} NGD Technolab. All rights reserved.
        </div>
    </div>
</div>

<script>
function togglePw(btn) {
    const pw = document.getElementById('password');
    const show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    btn.querySelector('svg').innerHTML = show
        ? '<line x1="1" y1="1" x2="23" y2="23"/><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>'
        : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
}
</script>
</body>
</html>
