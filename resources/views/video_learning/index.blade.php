@extends('layouts.admin')

@section('title', 'Video Learning with Word')
@section('page-title', 'Video Learning with Word')

@section('styles')
<style>
    /* ── Header & Stats Bar ── */
    .module-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
    }
    .module-title-group h2 {
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.3px;
    }
    .module-title-group p {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 3px;
    }

    .stats-pills {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .stat-pill {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--surface);
        border: 1px solid var(--border);
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text);
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .stat-pill .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .dot-indigo { background: #4f46e5; }
    .dot-green  { background: #16a34a; }
    .dot-gray   { background: #94a3b8; }

    /* ── Controls Toolbar ── */
    .toolbar-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .toolbar-left {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        flex: 1;
        min-width: 280px;
    }
    .search-input-wrap {
        position: relative;
        flex: 1;
        max-width: 380px;
        min-width: 200px;
    }
    .search-input-wrap svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 17px;
        height: 17px;
        color: var(--text-muted);
        pointer-events: none;
    }
    .search-input {
        width: 100%;
        padding: 9px 14px 9px 38px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--surface2);
        font-size: 13.5px;
        color: var(--text);
        outline: none;
        transition: all 0.2s ease;
    }
    .search-input:focus {
        border-color: var(--primary);
        background: var(--surface);
        box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
    }
    .select-filter {
        padding: 9px 14px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--surface2);
        font-size: 13.5px;
        color: var(--text);
        outline: none;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .select-filter:focus {
        border-color: var(--primary);
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.18s ease;
        text-decoration: none;
    }
    .btn-primary {
        background: var(--primary);
        color: #fff;
        box-shadow: 0 2px 8px rgba(79,70,229,0.28);
    }
    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(79,70,229,0.35);
    }
    .btn-outline {
        background: var(--surface);
        border: 1px solid var(--border);
        color: var(--text);
    }
    .btn-outline:hover {
        background: var(--surface2);
    }

    /* ── Table Card ── */
    .table-container {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13.5px;
    }
    .data-table th {
        background: var(--surface2);
        padding: 13px 16px;
        font-weight: 600;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border);
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.8px;
    }
    .data-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        color: var(--text);
    }
    .data-table tr:last-child td {
        border-bottom: none;
    }
    .data-table tr.sortable-row {
        transition: background 0.15s ease, opacity 0.15s ease;
    }
    .data-table tr.sortable-row:hover td {
        background: #fcfdfe;
    }
    .data-table tr.sortable-row.dragging {
        opacity: 0.4;
        background: #ede9fe;
    }
    .data-table tr.sortable-row.drag-over td {
        border-top: 2px solid var(--primary);
    }

    .drag-row-handle {
        cursor: grab;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .drag-row-handle:hover {
        background: #e2e8f0;
        color: var(--text);
    }
    .drag-row-handle:active {
        cursor: grabbing;
    }

    /* ── Thumbnail Cell (Portrait 9:16) ── */
    .thumb-wrap {
        width: 50px;
        height: 80px;
        aspect-ratio: 9 / 16;
        border-radius: 10px;
        overflow: hidden;
        background: #0f172a;
        position: relative;
        cursor: pointer;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }
    .thumb-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.2s ease;
    }
    .thumb-wrap:hover img {
        transform: scale(1.08);
    }
    .thumb-overlay-icon {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s;
        color: #fff;
    }
    .thumb-wrap:hover .thumb-overlay-icon {
        opacity: 1;
    }

    /* ── Video Info ── */
    .video-info-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .video-file-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
    }
    .btn-play-trigger {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 6px;
        background: #eef2ff;
        color: #4338ca;
        border: none;
        font-size: 11.5px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 3px;
        width: fit-content;
        transition: background 0.15s;
    }
    .btn-play-trigger:hover {
        background: #e0e7ff;
    }

    /* ── JSON Preview Pill ── */
    .json-badge-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid var(--border);
        font-size: 12px;
        font-family: monospace;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.15s;
    }
    .json-badge-btn:hover {
        background: #e2e8f0;
        border-color: #cbd5e1;
    }

    /* ── Switch Toggle ── */
    .switch-toggle {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        user-select: none;
    }
    .switch-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
        position: absolute;
    }
    .switch-track {
        width: 44px;
        height: 24px;
        background-color: #cbd5e1;
        border-radius: 30px;
        transition: background-color 0.25s ease;
        position: relative;
    }
    .switch-thumb {
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        background: #ffffff;
        border-radius: 50%;
        transition: transform 0.25s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.25);
    }
    .switch-toggle input:checked + .switch-track {
        background-color: #10b981;
    }
    .switch-toggle input:checked + .switch-track .switch-thumb {
        transform: translateX(20px);
    }
    .status-text-badge {
        font-size: 11.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 12px;
    }
    .status-badge-visible {
        background: #dcfce7;
        color: #15803d;
    }
    .status-badge-hidden {
        background: #f1f5f9;
        color: #64748b;
    }

    /* ── Actions ── */
    .action-btn-group {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
    }
    .action-btn:hover {
        background: var(--surface2);
        color: var(--text);
    }
    .action-btn.edit-btn:hover {
        background: #ede9fe;
        color: #6d28d9;
        border-color: #ddd6fe;
    }
    .action-btn.del-btn:hover {
        background: #fee2e2;
        color: #dc2626;
        border-color: #fecaca;
    }

    /* ── Empty State ── */
    .empty-state {
        text-align: center;
        padding: 56px 20px;
    }
    .empty-icon {
        width: 58px;
        height: 58px;
        border-radius: 16px;
        background: #ede9fe;
        color: #6d28d9;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }
    .empty-state h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
    }
    .empty-state p {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
        margin-bottom: 18px;
    }

    /* ── Pagination ── */
    .pagination-bar {
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-top: 1px solid var(--border);
        background: var(--surface);
        flex-wrap: wrap;
        gap: 10px;
    }
    /* Custom AJAX pagination buttons */
    .ajax-pagination {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: wrap;
    }
    .ajax-pagination .page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        height: 34px;
        padding: 0 10px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        color: var(--text-muted);
        background: transparent;
        border: 1px solid var(--border);
        transition: background 0.15s, color 0.15s, border-color 0.15s;
        user-select: none;
    }
    .ajax-pagination .page-btn:hover:not(.disabled):not(.active) {
        background: var(--hover);
        color: var(--text);
        border-color: var(--primary);
    }
    .ajax-pagination .page-btn.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        cursor: default;
        pointer-events: none;
    }
    .ajax-pagination .page-btn.disabled {
        opacity: 0.38;
        cursor: default;
        pointer-events: none;
    }
    /* Loading overlay for AJAX page change */
    #tableWrapper {
        position: relative;
        min-height: 120px;
    }
    #tableWrapper.loading::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(255,255,255,0.45);
        border-radius: 12px;
        z-index: 10;
    }
    #tableWrapper.loading::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 34px;
        height: 34px;
        margin: -17px 0 0 -17px;
        border: 3px solid var(--border);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
        z-index: 11;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Modals Backdrop & Frame ── */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,0.55);
        backdrop-filter: blur(4px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        overflow-y: auto;
    }
    .modal-backdrop.active {
        display: flex;
    }
    .modal-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 18px;
        width: 100%;
        max-width: 620px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.18);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        animation: modalSlideIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes modalSlideIn {
        from { opacity: 0; transform: translateY(12px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .modal-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
    }
    .modal-close-btn {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 6px;
        border-radius: 8px;
        display: flex;
        transition: all 0.15s;
    }
    .modal-close-btn:hover {
        background: var(--surface2);
        color: var(--text);
    }
    .modal-body {
        padding: 24px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }
    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        background: var(--surface2);
    }

    /* ── Toast Alert ── */
    .toast-container {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 2000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .toast {
        background: #1e293b;
        color: #fff;
        padding: 12px 18px;
        border-radius: 10px;
        font-size: 13.5px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 10px;
        animation: toastIn 0.25s ease;
    }
    .toast.success { background: #065f46; color: #a7f3d0; }
    .toast.error { background: #991b1b; color: #fecaca; }
    @keyframes toastIn {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')

<!-- Toast Notification Container -->
<div class="toast-container" id="toastContainer">
    @if(session('success'))
        <div class="toast success" data-auto-dismiss="5000">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="toast error" data-auto-dismiss="5000">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
</div>

<!-- Header Section -->
<div class="module-header">
    <div class="module-title-group">
        <h2>Video Learning with Word</h2>
        <p>Manage learning videos, thumbnails, JSON data, and API visibility. Drag rows to reorder sequence.</p>
    </div>

    <div class="stats-pills">
        <div class="stat-pill">
            <span class="dot dot-indigo"></span>
            <span>Total: <strong>{{ $stats['total'] }}</strong></span>
        </div>
        <div class="stat-pill">
            <span class="dot dot-green"></span>
            <span>Show (API Active): <strong>{{ $stats['visible'] }}</strong></span>
        </div>
        <div class="stat-pill">
            <span class="dot dot-gray"></span>
            <span>Hide (Hidden): <strong>{{ $stats['hidden'] }}</strong></span>
        </div>
    </div>
</div>

<!-- Toolbar Card (Search, Filter, Create Link) -->
<div class="toolbar-card">
    <form method="GET" action="{{ route('video-learning.index') }}" class="toolbar-left" id="filterForm">
        <div class="search-input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="search" class="search-input" placeholder="Search by video filename or JSON..." value="{{ request('search') }}">
        </div>

        <select name="status" class="select-filter">
            <option value="">All Statuses</option>
            <option value="show" {{ request('status') === 'show' ? 'selected' : '' }}>Show (Visible in API)</option>
            <option value="hide" {{ request('status') === 'hide' ? 'selected' : '' }}>Hide (Hidden in API)</option>
        </select>

        @if(request('search') || request('status'))
            <a href="{{ route('video-learning.index') }}" class="btn-action btn-outline reset-filter-btn" style="padding: 8px 12px;" title="Reset filters">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reset
            </a>
        @endif
    </form>

    <div>
        <a href="{{ route('video-learning.create') }}" class="btn-action btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add New Video Word
        </a>
    </div>
</div>

<!-- AJAX-rendered table+pagination wrapper -->
<div id="tableWrapper">
    @include('video_learning._list', ['items' => $items])
</div>

<!-- ======================================================== -->
<!-- MODAL: VIDEO PLAYER (Portrait 9:16)                       -->
<!-- ======================================================== -->
<div class="modal-backdrop" id="videoModal">
    <div class="modal-card" style="max-width: 420px; background: #0b0f19; border-radius: 20px;">
        <div class="modal-header" style="border-color: #1e293b;">
            <span class="modal-title" id="videoModalTitle" style="color: #fff;">Video Player</span>
            <button class="modal-close-btn" onclick="closeVideoPlayer()" style="color: #94a3b8;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div style="padding: 0; background: #000; display: flex; align-items: center; justify-content: center; min-height: 480px;">
            <video id="mainVideoPlayer" controls playsinline style="width: 100%; max-height: 75vh; aspect-ratio: 9/16; object-fit: contain;"></video>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: JSON VIEWER                                        -->
<!-- ======================================================== -->
<div class="modal-backdrop" id="jsonModal">
    <div class="modal-card" style="max-width: 580px;">
        <div class="modal-header">
            <span class="modal-title">JSON Payload Viewer</span>
            <button class="modal-close-btn" onclick="closeModal('jsonModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding: 16px;">
            <pre id="jsonModalContent" style="background:#0f172a; color:#38bdf8; padding: 18px; border-radius: 12px; font-size: 13px; font-family: monospace; overflow-x: auto; max-height: 380px; white-space: pre-wrap; word-break: break-all; margin: 0;"></pre>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-action btn-outline" onclick="copyJsonModalContent()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                Copy JSON
            </button>
            <button type="button" class="btn-action btn-primary" onclick="closeModal('jsonModal')">Close</button>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: IMAGE LIGHTBOX (Portrait 9:16)                     -->
<!-- ======================================================== -->
<div class="modal-backdrop" id="imageModal" onclick="closeModal('imageModal')">
    <div class="modal-card" style="max-width: 440px; background: transparent; border: none; box-shadow: none;" onclick="event.stopPropagation()">
        <div style="position: relative; text-align: center;">
            <img id="lightboxImg" src="" alt="Enlarged Thumbnail" style="max-width: 100%; max-height: 80vh; aspect-ratio: 9/16; object-fit: contain; border-radius: 16px; box-shadow: 0 25px 50px rgba(0,0,0,0.6); border: 2px solid rgba(255,255,255,0.15);">
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: DELETE CONFIRMATION                                -->
<!-- ======================================================== -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-card" style="max-width: 440px;">
        <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
            <div style="width: 42px; height: 42px; border-radius: 12px; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </div>
            <button class="modal-close-btn" onclick="closeModal('deleteModal')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding-top: 10px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--text);">Delete Video Learning Record?</h3>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                Are you sure you want to delete <strong id="deleteItemTitle" style="color:var(--text);"></strong>? This will delete the record and its physical video and thumbnail files from storage.
            </p>
        </div>
        <div class="modal-footer" style="background: transparent; border-top: none;">
            <form action="" method="POST" id="deleteForm">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-action btn-outline" onclick="closeModal('deleteModal')">Cancel</button>
                <button type="submit" class="btn-action" style="background: #ef4444; color: #fff;">Delete Permanently</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const REORDER_URL = "{{ route('video-learning.reorder') }}";
const LIST_AJAX_URL = "{{ route('video-learning.list-ajax') }}";

// ── AJAX Pagination ──────────────────────────────────────────
// Reads current search/status params from the filter form,
// fetches only the table partial, swaps it in, re-inits D&D.
// URL in the address bar NEVER changes.
function loadPage(page) {
    const wrapper = document.getElementById('tableWrapper');
    const form    = document.getElementById('filterForm');
    const params  = new URLSearchParams(new FormData(form));
    params.set('page', page);

    wrapper.classList.add('loading');

    fetch(LIST_AJAX_URL + '?' + params.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => {
        if (!res.ok) throw new Error('Network error');
        return res.text();
    })
    .then(html => {
        wrapper.innerHTML = html;
        wrapper.classList.remove('loading');
        bindPaginationLinks();
        initTableDragAndDrop();
        // scroll table into view if needed
        wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    })
    .catch(() => {
        wrapper.classList.remove('loading');
        showToast('Failed to load page. Please try again.', 'error');
    });
}

// Attach click handlers to all .ajax-page links inside wrapper
function bindPaginationLinks() {
    document.querySelectorAll('#tableWrapper .ajax-page').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const page = parseInt(this.dataset.page, 10);
            if (!isNaN(page)) loadPage(page);
        });
    });
}

// Also intercept filter form submit → load page 1 via AJAX
document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        // Search input: debounce 400ms
        const searchInput = filterForm.querySelector('input[name="search"]');
        let debounceTimer;
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => loadPage(1), 400);
            });
            // Prevent default form submit on Enter
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') e.preventDefault();
            });
        }
        // Status select already has onchange submit; override it
        const statusSelect = filterForm.querySelector('select[name="status"]');
        if (statusSelect) {
            statusSelect.addEventListener('change', () => loadPage(1));
        }
        // Prevent normal form submit entirely
        filterForm.addEventListener('submit', e => e.preventDefault());
    }

    // Reset button: clear inputs then reload page 1
    document.querySelectorAll('.reset-filter-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                filterForm.querySelector('input[name="search"]').value = '';
                filterForm.querySelector('select[name="status"]').value = '';
            }
            loadPage(1);
        });
    });

    // Bind initial pagination links and drag-and-drop
    bindPaginationLinks();
    initTableDragAndDrop();

    // Auto dismiss session flash toasts
    document.querySelectorAll('.toast').forEach(toast => {
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(16px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    });
});

// Helper: Show Toast Notification (strictly auto-remove after 5s)
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            ${type === 'success' ? '<polyline points="20 6 9 17 4 12"/>' : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'}
        </svg>
        <span>${message}</span>
    `;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(16px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Modal open/close helpers
function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
    document.body.style.overflow = '';
}

// Video Player Modal
function openVideoPlayer(url, title) {
    const video = document.getElementById('mainVideoPlayer');
    document.getElementById('videoModalTitle').textContent = title || 'Video Player';
    video.src = url;
    openModal('videoModal');
    video.play().catch(() => {});
}
function closeVideoPlayer() {
    const video = document.getElementById('mainVideoPlayer');
    video.pause();
    video.src = '';
    closeModal('videoModal');
}

// JSON Modal
function openJsonModal(id) {
    const raw = document.getElementById(`json-store-${id}`).value;
    let formatted = raw;
    try {
        formatted = JSON.stringify(JSON.parse(raw), null, 2);
    } catch (e) {}
    document.getElementById('jsonModalContent').textContent = formatted;
    openModal('jsonModal');
}
function copyJsonModalContent() {
    const text = document.getElementById('jsonModalContent').textContent;
    navigator.clipboard.writeText(text).then(() => {
        showToast('JSON copied to clipboard!', 'success');
    });
}

// Lightbox
function openImageModal(url) {
    document.getElementById('lightboxImg').src = url;
    openModal('imageModal');
}

// Delete Confirmation
function openDeleteModal(id, title) {
    document.getElementById('deleteItemTitle').textContent = `"${title || 'this item'}"`;
    document.getElementById('deleteForm').action = `{{ url('/video-learning-words') }}/${id}`;
    openModal('deleteModal');
}

// Instant AJAX Toggle Visibility
function toggleVisibility(id, checkbox) {
    const badge = document.getElementById(`status-badge-${id}`);
    const originalChecked = checkbox.checked;

    fetch(`{{ url('/video-learning-words') }}/${id}/toggle-visibility`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            checkbox.checked = data.is_visible;
            if (data.is_visible) {
                badge.className = 'status-text-badge status-badge-visible';
                badge.textContent = 'Show';
            } else {
                badge.className = 'status-text-badge status-badge-hidden';
                badge.textContent = 'Hide';
            }
            showToast(data.message, 'success');
        } else {
            checkbox.checked = !originalChecked;
            showToast('Could not update status.', 'error');
        }
    })
    .catch(err => {
        checkbox.checked = !originalChecked;
        showToast('Network error while updating status.', 'error');
    });
}

// ── Table Rows Drag and Drop Reordering ──
let draggedRow = null;

function initTableDragAndDrop() {
    const rows = document.querySelectorAll('.sortable-row');
    const tbody = document.getElementById('sortableTbody');
    if (!tbody) return;

    rows.forEach(row => {
        row.addEventListener('dragstart', (e) => {
            draggedRow = row;
            row.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        row.addEventListener('dragend', () => {
            if (draggedRow) {
                draggedRow.classList.remove('dragging');
                draggedRow = null;
            }
            document.querySelectorAll('.sortable-row').forEach(r => r.classList.remove('drag-over'));
            saveTableReorder();
        });

        row.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (!draggedRow || draggedRow === row) return;

            const rect = row.getBoundingClientRect();
            const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
            tbody.insertBefore(draggedRow, next ? row.nextSibling : row);
        });
    });
}

function saveTableReorder() {
    const rows = document.querySelectorAll('.sortable-row');
    const ids = Array.from(rows).map(r => r.dataset.id);

    // Update row numbers locally
    rows.forEach((r, idx) => {
        const numCell = r.querySelector('.row-num');
        if (numCell) numCell.textContent = idx + 1;
    });

    fetch(REORDER_URL, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ordered_ids: ids })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Sequence updated successfully!', 'success');
        }
    })
    .catch(err => {
        showToast('Failed to save sequence order.', 'error');
    });
}

// initTableDragAndDrop is called from the main DOMContentLoaded block above
// and re-called after each AJAX page swap.
</script>
@endsection
