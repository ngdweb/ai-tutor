@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('styles')
<style>
    /* ── Stat Cards ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 20px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,0.08); }

    .stat-icon {
        width: 50px; height: 50px;
        border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stat-icon svg { width: 22px; height: 22px; }

    .ic-indigo { background: #ede9fe; color: #6d28d9; }
    .ic-green  { background: #dcfce7; color: #16a34a; }
    .ic-blue   { background: #dbeafe; color: #2563eb; }
    .ic-orange { background: #ffedd5; color: #ea580c; }

    .stat-value { font-size: 26px; font-weight: 700; color: var(--text); line-height: 1; }
    .stat-label { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
    .stat-badge {
        display: inline-flex; align-items: center; gap: 3px;
        margin-top: 6px; font-size: 11.5px; font-weight: 600;
        color: #16a34a; background: #dcfce7;
        padding: 2px 8px; border-radius: 20px;
    }

    /* ── Bottom Grid ── */
    .bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 22px;
    }
    .panel-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border);
    }
    .panel-title { font-size: 14px; font-weight: 700; color: var(--text); }
    .panel-chip {
        font-size: 11px; font-weight: 600;
        padding: 3px 10px; border-radius: 20px;
        background: #ede9fe; color: #6d28d9;
    }
    .panel-chip.green { background: #dcfce7; color: #16a34a; }

    /* ── Activity List ── */
    .activity-list { display: flex; flex-direction: column; gap: 2px; }
    .activity-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 8px; border-radius: 9px;
        transition: background 0.15s;
        cursor: default;
    }
    .activity-item:hover { background: var(--surface2); }

    .act-dot {
        width: 34px; height: 34px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .act-dot svg { width: 15px; height: 15px; }
    .ad-indigo { background: #ede9fe; color: #6d28d9; }
    .ad-green  { background: #dcfce7; color: #16a34a; }
    .ad-purple { background: #fae8ff; color: #9333ea; }
    .ad-orange { background: #ffedd5; color: #ea580c; }
    .ad-teal   { background: #ccfbf1; color: #0d9488; }

    .act-text  { flex: 1; font-size: 13.5px; color: var(--text); }
    .act-time  { font-size: 11.5px; color: var(--text-muted); white-space: nowrap; }

    /* ── Mini Chart ── */
    .mini-stats { display: flex; gap: 12px; margin-bottom: 18px; }
    .mini-stat {
        flex: 1; background: var(--surface2); border: 1px solid var(--border);
        border-radius: 10px; padding: 12px 14px; text-align: center;
    }
    .mini-stat-val { font-size: 22px; font-weight: 700; color: #4f46e5; }
    .mini-stat-lbl { font-size: 11.5px; color: var(--text-muted); margin-top: 3px; }
    .mini-stat.green .mini-stat-val { color: #16a34a; }

    .chart-bars {
        display: flex; align-items: flex-end; gap: 8px;
        height: 90px;
    }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px; }
    .bar-fill {
        width: 100%; border-radius: 5px 5px 0 0;
        background: #e2e8f0;
        transition: background 0.2s;
    }
    .bar-fill.active { background: linear-gradient(180deg, #818cf8, #4f46e5); }
    .bar-lbl { font-size: 10px; color: var(--text-muted); }

    @media (max-width: 900px) { .bottom-grid { grid-template-columns: 1fr; } }
    @media (max-width: 580px) {
        .stats-grid { grid-template-columns: 1fr 1fr; }
        .stat-value  { font-size: 22px; }
    }
    @media (max-width: 380px) { .stats-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

<!-- Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon ic-indigo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="23 7 16 12 23 17 23 7"/>
                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ $stats['total_videos'] }}</div>
            <div class="stat-label">Video Learning Words</div>
            <span class="stat-badge">
                <a href="{{ route('video-learning.index') }}" style="color:inherit; text-decoration:none;">View Module →</a>
            </span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ $stats['visible_videos'] }}</div>
            <div class="stat-label">Show (API Active)</div>
            <span class="stat-badge">↑ Visible in API</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon ic-orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
        </div>
        <div>
            <div class="stat-value">{{ $stats['hidden_videos'] }}</div>
            <div class="stat-label">Hide (Hidden)</div>
            <span class="stat-badge" style="background:#fee2e2; color:#dc2626;">Hidden from API</span>
        </div>
    </div>
</div>

<!-- Bottom Panels -->
<div class="bottom-grid">
    <!-- Activity -->
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Recent Activity</span>
            <span class="panel-chip green">Live</span>
        </div>
        <div class="activity-list">
            @foreach ($recentActivity as $item)
            <div class="activity-item">
                <div class="act-dot
                    @if($item['color']==='blue') ad-indigo
                    @elseif($item['color']==='green') ad-green
                    @elseif($item['color']==='purple') ad-purple
                    @elseif($item['color']==='orange') ad-orange
                    @else ad-teal @endif">
                    @if($item['icon']==='user')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    @elseif($item['icon']==='check')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    @elseif($item['icon']==='video')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                    @elseif($item['icon']==='database')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                    @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    @endif
                </div>
                <div class="act-text">{{ $item['text'] }}</div>
                <div class="act-time">{{ $item['time'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Quick Access / Recent Videos -->
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Latest Video Learning Words</span>
            <a href="{{ route('video-learning.index') }}" class="panel-chip" style="text-decoration:none;">Manage All →</a>
        </div>
        
        @if(isset($recentVideoItems) && $recentVideoItems->count() > 0)
        <div class="activity-list">
            @foreach($recentVideoItems as $v)
            <div class="activity-item" style="justify-content: space-between;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <img src="{{ $v->thumbnail_url }}" alt="{{ $v->title }}" style="width:36px; height:24px; border-radius:4px; object-fit:cover; background:#0f172a;" onerror="this.style.display='none'">
                    <div>
                        <div style="font-size:13.5px; font-weight:600; color:var(--text);">{{ $v->title }}</div>
                        <div style="font-size:11.5px; color:var(--text-muted);">{{ $v->created_at?->diffForHumans() }}</div>
                    </div>
                </div>
                <span class="panel-chip {{ $v->is_visible ? 'green' : '' }}" style="font-size:10.5px;">
                    {{ $v->is_visible ? 'Show' : 'Hide' }}
                </span>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align:center; padding: 28px 10px; color:var(--text-muted); font-size:13px;">
            No video learning items yet.
            <div style="margin-top: 10px;">
                <a href="{{ route('video-learning.index') }}" style="color:var(--primary); font-weight:600; text-decoration:none;">+ Create First Item</a>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
