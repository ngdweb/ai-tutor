@extends('layouts.admin')

@section('title', 'Profile')
@section('page-title', 'My Profile')

@section('styles')
<style>
    .profile-grid {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 20px;
    }

    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 28px;
    }

    .profile-avatar-section { text-align: center; }
    .profile-avatar {
        width: 100px; height: 100px;
        background: linear-gradient(135deg, #6366f1, #a855f7);
        border-radius: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 16px;
        box-shadow: 0 8px 32px rgba(99,102,241,0.4);
    }
    .profile-name { font-size: 20px; font-weight: 700; color: var(--text); }
    .profile-role {
        display: inline-block;
        margin-top: 8px;
        font-size: 12px;
        background: rgba(99,102,241,0.12);
        color: #818cf8;
        border: 1px solid rgba(99,102,241,0.2);
        border-radius: 20px;
        padding: 4px 14px;
        font-weight: 500;
    }

    .info-list { margin-top: 24px; display: flex; flex-direction: column; gap: 14px; }
    .info-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--surface2);
        border-radius: 10px;
    }
    .info-icon { color: var(--text-muted); flex-shrink: 0; }
    .info-icon svg { width: 17px; height: 17px; }
    .info-label { font-size: 11.5px; color: var(--text-muted); }
    .info-value { font-size: 13.5px; font-weight: 500; color: var(--text); }

    .section-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border);
    }

    .field-group { margin-bottom: 18px; }
    .field-label { font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; display: block; }
    .field-input {
        width: 100%;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 14px;
        color: var(--text);
        font-family: inherit;
        transition: border-color 0.2s;
        outline: none;
    }
    .field-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
    .field-input:read-only { opacity: 0.6; cursor: not-allowed; }

    .btn-save {
        background: linear-gradient(135deg, #6366f1, #a855f7);
        border: none;
        border-radius: 10px;
        padding: 11px 24px;
        color: #fff;
        font-size: 14px;
        font-weight: 600;
        font-family: inherit;
        cursor: pointer;
        box-shadow: 0 4px 16px rgba(99,102,241,0.3);
        transition: all 0.2s;
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99,102,241,0.45); }

    .security-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        background: var(--surface2);
        border-radius: 12px;
        margin-bottom: 12px;
    }
    .security-label { font-size: 14px; font-weight: 500; color: var(--text); }
    .security-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
    .badge-ok { font-size: 12px; color: #4ade80; background: rgba(34,197,94,0.1); padding: 4px 10px; border-radius: 20px; font-weight: 500; }

    @media (max-width: 900px) {
        .profile-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="profile-grid">
    <div>
        <div class="panel" style="margin-bottom:20px;">
            <div class="profile-avatar-section">
                <div class="profile-avatar">{{ substr($user->name, 0, 1) }}</div>
                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-role">Super Admin</div>
            </div>
            <div class="info-list">
                <div class="info-row">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <div class="info-label">Password</div>
                        <div class="info-value">•••••••• (hashed)</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div>
                        <div class="info-label">Joined</div>
                        <div class="info-value">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="section-title">Security</div>
            <div class="security-card">
                <div>
                    <div class="security-label">2FA Authentication</div>
                    <div class="security-sub">Two-factor auth status</div>
                </div>
                <span class="badge-ok">Active</span>
            </div>
            <div class="security-card">
                <div>
                    <div class="security-label">Password Hash</div>
                    <div class="security-sub">bcrypt (12 rounds)</div>
                </div>
                <span class="badge-ok">Secure</span>
            </div>
            <div class="security-card">
                <div>
                    <div class="security-label">Session</div>
                    <div class="security-sub">Active — expires in 120 min</div>
                </div>
                <span class="badge-ok">Online</span>
            </div>
        </div>
    </div>

    <div>
        <div class="panel" style="margin-bottom:20px;">
            <div class="section-title">Account Information</div>
            <div class="field-group">
                <label class="field-label">Full Name</label>
                <input type="text" class="field-input" value="{{ $user->name }}" readonly>
            </div>
            <div class="field-group">
                <label class="field-label">Email Address</label>
                <input type="email" class="field-input" value="{{ $user->email }}" readonly>
            </div>
            <div class="field-group">
                <label class="field-label">Role</label>
                <input type="text" class="field-input" value="Super Administrator" readonly>
            </div>
            <div class="field-group">
                <label class="field-label">Account ID</label>
                <input type="text" class="field-input" value="#ADM-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}" readonly>
            </div>
            <p style="font-size:12.5px;color:var(--text-muted);margin-bottom:16px;">This is a static admin account. Fields are read-only.</p>
            <button class="btn-save" disabled style="opacity:0.5;cursor:not-allowed;">Save Changes</button>
        </div>

        <div class="panel">
            <div class="section-title">System Info</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="background:var(--surface2);border-radius:10px;padding:14px;">
                    <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:4px;">PHP Version</div>
                    <div style="font-size:14px;font-weight:600;color:var(--text);">{{ phpversion() }}</div>
                </div>
                <div style="background:var(--surface2);border-radius:10px;padding:14px;">
                    <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:4px;">Laravel</div>
                    <div style="font-size:14px;font-weight:600;color:var(--text);">{{ app()->version() }}</div>
                </div>
                <div style="background:var(--surface2);border-radius:10px;padding:14px;">
                    <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:4px;">Database</div>
                    <div style="font-size:14px;font-weight:600;color:var(--text);">SQLite</div>
                </div>
                <div style="background:var(--surface2);border-radius:10px;padding:14px;">
                    <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:4px;">Environment</div>
                    <div style="font-size:14px;font-weight:600;color:#4ade80;">{{ app()->environment() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
