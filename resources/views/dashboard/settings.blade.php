@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('styles')
<style>
    .settings-layout {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 20px;
    }

    .settings-tabs {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 12px;
        height: fit-content;
    }
    .tab-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        color: var(--text-muted);
        font-size: 13.5px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 2px;
    }
    .tab-item:hover { background: var(--surface2); color: var(--text); }
    .tab-item.active { background: var(--primary-light); color: var(--primary); }
    .tab-icon svg { width: 16px; height: 16px; }

    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 28px;
        margin-bottom: 20px;
    }
    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 6px;
    }
    .section-sub { font-size: 13px; color: var(--text-muted); margin-bottom: 24px; }

    .toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 0;
        border-bottom: 1px solid var(--border);
    }
    .toggle-row:last-child { border-bottom: none; }
    .toggle-label { font-size: 14px; font-weight: 500; color: var(--text); }
    .toggle-desc { font-size: 12px; color: var(--text-muted); margin-top: 3px; }

    .toggle {
        position: relative;
        width: 44px;
        height: 24px;
        flex-shrink: 0;
    }
    .toggle input { display: none; }
    .toggle-track {
        position: absolute;
        inset: 0;
        background: var(--surface2);
        border-radius: 12px;
        cursor: pointer;
        transition: background 0.25s;
        border: 1px solid var(--border);
    }
    .toggle-track::after {
        content: '';
        position: absolute;
        width: 18px; height: 18px;
        border-radius: 50%;
        background: #fff;
        top: 2px; left: 2px;
        transition: transform 0.25s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    .toggle input:checked + .toggle-track { background: var(--primary); border-color: var(--primary); }
    .toggle input:checked + .toggle-track::after { transform: translateX(20px); }

    .field-group { margin-bottom: 18px; }
    .field-label { font-size: 13px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; display: block; }
    .field-select, .field-input {
        width: 100%;
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 14px;
        color: var(--text);
        font-family: inherit;
        outline: none;
        appearance: none;
        transition: border-color 0.2s;
    }
    .field-select:focus, .field-input:focus { border-color: var(--primary); }
    .field-select option { background: #1a1d27; }

    .btn-row { display: flex; gap: 10px; margin-top: 8px; }
    .btn-primary {
        background: linear-gradient(135deg, #6366f1, #a855f7);
        border: none; border-radius: 10px; padding: 10px 22px;
        color: #fff; font-size: 13.5px; font-weight: 600; font-family: inherit;
        cursor: pointer; box-shadow: 0 4px 14px rgba(99,102,241,0.3); transition: all 0.2s;
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.4); }
    .btn-ghost {
        background: var(--surface2); border: 1px solid var(--border);
        border-radius: 10px; padding: 10px 22px;
        color: var(--text-muted); font-size: 13.5px; font-weight: 500; font-family: inherit;
        cursor: pointer; transition: all 0.2s;
    }
    .btn-ghost:hover { background: var(--border); color: var(--text); }

    @media (max-width: 768px) {
        .settings-layout { grid-template-columns: 1fr; }
        .settings-tabs { display: flex; gap: 4px; overflow-x: auto; padding: 8px; }
        .tab-item { white-space: nowrap; flex-shrink: 0; }
    }
</style>
@endsection

@section('content')
<div class="settings-layout">
    <div class="settings-tabs">
        <div class="tab-item active">
            <span class="tab-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
            General
        </div>
        <div class="tab-item">
            <span class="tab-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg></span>
            Notifications
        </div>
        <div class="tab-item">
            <span class="tab-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
            Security
        </div>
        <div class="tab-item">
            <span class="tab-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></span>
            Database
        </div>
    </div>

    <div>
        <div class="panel">
            <div class="section-title">General Settings</div>
            <div class="section-sub">Manage your application preferences and configuration.</div>

            <div class="field-group">
                <label class="field-label">Application Name</label>
                <input type="text" class="field-input" value="AdminPanel" readonly>
            </div>
            <div class="field-group">
                <label class="field-label">Timezone</label>
                <select class="field-select">
                    <option selected>UTC</option>
                    <option>Asia/Kolkata</option>
                    <option>America/New_York</option>
                    <option>Europe/London</option>
                </select>
            </div>
            <div class="field-group">
                <label class="field-label">Language</label>
                <select class="field-select">
                    <option selected>English (en)</option>
                    <option>Hindi (hi)</option>
                </select>
            </div>

            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Debug Mode</div>
                    <div class="toggle-desc">Show error details (disable in production)</div>
                </div>
                <label class="toggle">
                    <input type="checkbox" checked>
                    <span class="toggle-track"></span>
                </label>
            </div>
            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Maintenance Mode</div>
                    <div class="toggle-desc">Take site offline for visitors</div>
                </div>
                <label class="toggle">
                    <input type="checkbox">
                    <span class="toggle-track"></span>
                </label>
            </div>

            <div class="btn-row" style="margin-top:20px;">
                <button class="btn-primary">Save Changes</button>
                <button class="btn-ghost">Reset</button>
            </div>
        </div>

        <div class="panel">
            <div class="section-title">Notification Preferences</div>
            <div class="section-sub">Choose when you want to receive alerts.</div>

            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Email Notifications</div>
                    <div class="toggle-desc">Receive alerts to admin@gmail.com</div>
                </div>
                <label class="toggle">
                    <input type="checkbox" checked>
                    <span class="toggle-track"></span>
                </label>
            </div>
            <div class="toggle-row">
                <div>
                    <div class="toggle-label">Security Alerts</div>
                    <div class="toggle-desc">Notify on suspicious login attempts</div>
                </div>
                <label class="toggle">
                    <input type="checkbox" checked>
                    <span class="toggle-track"></span>
                </label>
            </div>
            <div class="toggle-row">
                <div>
                    <div class="toggle-label">System Updates</div>
                    <div class="toggle-desc">Get notified when updates are available</div>
                </div>
                <label class="toggle">
                    <input type="checkbox">
                    <span class="toggle-track"></span>
                </label>
            </div>

            <div class="btn-row" style="margin-top:20px;">
                <button class="btn-primary">Save Preferences</button>
            </div>
        </div>
    </div>
</div>
@endsection
