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

    /* Category tag in table */
    .category-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 12px; background: #eef2ff; color: #4338ca; font-size: 12px; font-weight: 600; max-width: 100%; }
    .category-tag svg { flex-shrink: 0; }

    /* Set Index reorder list */
    .vreorder-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
    .vreorder-item { display: flex; align-items: center; gap: 12px; padding: 9px 14px; border: 1px solid var(--border); border-radius: 10px; background: var(--surface2); cursor: grab; transition: background 0.15s, box-shadow 0.15s, opacity 0.15s; }
    .vreorder-item:hover { background: #f5f3ff; border-color: #ddd6fe; }
    .vreorder-item.dragging { opacity: 0.45; background: #ede9fe; cursor: grabbing; }
    .vreorder-item.drag-over { border-top: 2px solid var(--primary); }
    .vreorder-grip { color: var(--text-muted); display: flex; }
    .vreorder-seq { min-width: 24px; height: 24px; border-radius: 6px; background: #eef2ff; color: #4338ca; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
    .vreorder-thumb { width: 32px; height: 46px; border-radius: 6px; overflow: hidden; background: #0f172a; flex-shrink: 0; border: 1px solid var(--border); }
    .vreorder-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .vreorder-name { flex: 1; font-size: 13.5px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .vreorder-ep { font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 12px; background: #fef3c7; color: #b45309; white-space: nowrap; }
    .vreorder-badge { font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 12px; }

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

        <select name="category_id" class="select-filter">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>

        @if(request('search') || request('status') || request('category_id'))
            <a href="{{ route('video-learning.index') }}" class="btn-action btn-outline reset-filter-btn" style="padding: 8px 12px;" title="Reset filters">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reset
            </a>
        @endif
    </form>

    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="btn-action btn-outline" onclick="openVideoReorderModal()" title="Set video sequence by category">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="9" y2="18"/><polyline points="17 10 21 14 17 18"/></svg>
            Set Index
        </button>
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
    @fragment('videoTable')
    <!-- Main Table Card (AJAX-refreshable fragment) -->
    <div class="table-container">
        @if($items->count() > 0)
        <table class="data-table" id="sortableTable">
            <thead>
                <tr>
                    <th style="width: 38px; text-align: center;" title="Drag rows to reorder">↕</th>
                    <th style="width: 50px;">#</th>
                    <th style="width: 95px;">Thumbnail</th>
                    <th>Video File</th>
                    <th style="width: 140px;">Category</th>
                    <th>JSON Data</th>
                    <th style="width: 140px;">API Visibility</th>
                    <th style="width: 100px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="sortableTbody">
                @foreach($items as $index => $item)
                <tr id="row-{{ $item->id }}" class="sortable-row" draggable="true" data-id="{{ $item->id }}">
                    <td style="text-align: center;">
                        <div class="drag-row-handle" title="Drag to reorder sequence">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                                <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                                <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                            </svg>
                        </div>
                    </td>
                    <td class="row-num" style="color: var(--text-muted); font-weight: 500;">
                        {{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}
                    </td>
                    <td>
                        <div class="thumb-wrap" onclick="openImageModal('{{ $item->thumbnail_url }}')" title="Click to enlarge thumbnail">
                            <img src="{{ $item->thumbnail_url }}" alt="Thumbnail" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'76\' height=\'46\'><rect width=\'100%\' height=\'100%\' fill=\'%231e293b\'/><text x=\'50%\' y=\'50%\' fill=\'%2394a3b8\' text-anchor=\'middle\' dy=\'.3em\' font-size=\'10\'>No Image</text></svg>'">
                            <div class="thumb-overlay-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="video-info-cell">
                            <span class="video-file-tag">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                {{ Str::limit($item->video_name ?: basename($item->video_path), 40) }}
                            </span>
                            <button class="btn-play-trigger" onclick="openVideoPlayer('{{ $item->video_url }}', '{{ addslashes($item->video_name ?: basename($item->video_path)) }}')">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                Play Video
                            </button>
                        </div>
                    </td>
                    <td>
                        <span class="category-tag">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            {{ $item->category?->name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <button class="json-badge-btn" onclick="openJsonModal({{ $item->id }})">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 18l6-6-6-6"/><path d="M8 6l-6 6 6 6"/></svg>
                            View JSON
                        </button>
                        <textarea id="json-store-{{ $item->id }}" style="display:none;">{{ $item->json_data }}</textarea>
                    </td>
                    <td>
                        <label class="switch-toggle" title="Toggle visibility in API">
                            <input type="checkbox" onchange="toggleVisibility({{ $item->id }}, this)" {{ $item->is_visible ? 'checked' : '' }}>
                            <span class="switch-track"><span class="switch-thumb"></span></span>
                            <span class="status-text-badge {{ $item->is_visible ? 'status-badge-visible' : 'status-badge-hidden' }}" id="status-badge-{{ $item->id }}">
                                {{ $item->is_visible ? 'Show' : 'Hide' }}
                            </span>
                        </label>
                    </td>
                    <td>
                        <div class="action-btn-group" style="justify-content: flex-end;">
                            <!-- Dedicated Edit Screen Link -->
                            <a href="{{ route('video-learning.edit', $item->id) }}" class="action-btn edit-btn" title="Edit Video Word">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <button class="action-btn del-btn" onclick="confirmDeleteVideo({{ $item->id }}, '{{ addslashes($item->video_name ?: basename($item->video_path)) }}')" title="Delete Video Word">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-bar">
            <div style="font-size: 13px; color: var(--text-muted);">
                Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries
            </div>
            @if($items->hasPages())
            <nav class="ajax-pagination" aria-label="Pagination">
                {{-- Previous --}}
                @if($items->onFirstPage())
                    <span class="page-btn disabled">&lsaquo; Prev</span>
                @else
                    <a href="{{ $items->previousPageUrl() }}" class="page-btn ajax-page" data-page="{{ $items->currentPage() - 1 }}">&lsaquo; Prev</a>
                @endif

                {{-- Numbered pages --}}
                @foreach($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                    @if($page == $items->currentPage())
                        <span class="page-btn active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-btn ajax-page" data-page="{{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}" class="page-btn ajax-page" data-page="{{ $items->currentPage() + 1 }}">Next &rsaquo;</a>
                @else
                    <span class="page-btn disabled">Next &rsaquo;</span>
                @endif
            </nav>
            @endif
        </div>
        @else
        <div class="empty-state">
            <div class="empty-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="23 7 16 12 23 17 23 7"/>
                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                </svg>
            </div>
            <h3>No Video Learning records found</h3>
            <p>Start by uploading video learning items with thumbnail and JSON data.</p>
            <a href="{{ route('video-learning.create') }}" class="btn-action btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add New Video Word
            </a>
        </div>
        @endif
    </div>
    @endfragment
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
            <pre id="jsonModalContent" style="background:#ffffff; color:#0f172a; border: 1px solid #e2e8f0; padding: 18px; border-radius: 12px; font-size: 13px; font-family: 'Consolas', 'Monaco', 'Courier New', monospace; overflow-x: auto; max-height: 380px; white-space: pre-wrap; word-break: break-all; margin: 0; line-height: 1.55; box-shadow: inset 0 1px 3px rgba(0,0,0,0.02);"></pre>
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
<!-- MODAL: SET INDEX (per-category video reorder)             -->
<!-- ======================================================== -->
<div class="modal-backdrop" id="videoReorderModal">
    <div class="modal-card" style="max-width: 680px; width: 92%;">
        <div class="modal-header" style="border-bottom: 1px solid var(--border);">
            <div>
                <div style="font-size: 16px; font-weight: 700; color: var(--text);">Set Video Index</div>
                <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 2px;">Pick a category, then drag videos to set their order. This sequence is used in the API response.</div>
            </div>
            <button class="modal-close-btn" onclick="closeModal('videoReorderModal')"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body" style="padding: 16px 20px;">
            <label style="display:block; font-size:12.5px; font-weight:600; color:var(--text); margin-bottom:8px;">Category</label>
            <select id="reorderCategorySelect" class="select-filter" style="width:100%; margin-bottom:16px;" onchange="loadCategoryVideosForReorder(this.value)">
                <option value="">— Choose a category —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}{{ $cat->is_active ? '' : ' (Off)' }}</option>
                @endforeach
            </select>

            <div id="reorderVideoListWrap" style="max-height: 62vh; min-height: 180px; overflow-y:auto;">
                <div style="text-align:center; color:var(--text-muted); font-size:13px; padding:24px 0;">Choose a category to load its videos.</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-action btn-outline" onclick="closeModal('videoReorderModal')">Close</button>
            <button type="button" class="btn-action btn-primary" id="saveVideoReorderBtn" onclick="saveVideoReorder()" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Sequence
            </button>
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
        // Category filter
        const categorySelect = filterForm.querySelector('select[name="category_id"]');
        if (categorySelect) {
            categorySelect.addEventListener('change', () => loadPage(1));
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
                const catSel = filterForm.querySelector('select[name="category_id"]');
                if (catSel) catSel.value = '';
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

// JSON Syntax Highlighter (Clean Light / White Mode)
function syntaxHighlightJson(json) {
    if (typeof json !== 'string') {
        json = JSON.stringify(json, undefined, 2);
    }
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
        let style = 'color: #0284c7;';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                style = 'color: #4f46e5; font-weight: 700;'; // Indigo keys
            } else {
                style = 'color: #059669; font-weight: 500;'; // Emerald strings
            }
        } else if (/true|false/.test(match)) {
            style = 'color: #d97706; font-weight: 700;'; // Amber booleans
        } else if (/null/.test(match)) {
            style = 'color: #94a3b8; font-style: italic;'; // Slate null
        } else {
            style = 'color: #ea580c; font-weight: 600;'; // Orange numbers
        }
        return '<span style="' + style + '">' + match + '</span>';
    });
}

// JSON Modal
function openJsonModal(id) {
    const raw = document.getElementById(`json-store-${id}`).value;
    let formatted = raw;
    try {
        const parsed = JSON.parse(raw);
        formatted = JSON.stringify(parsed, null, 2);
        document.getElementById('jsonModalContent').innerHTML = syntaxHighlightJson(formatted);
    } catch (e) {
        document.getElementById('jsonModalContent').textContent = formatted;
    }
    openModal('jsonModal');
}
function copyJsonModalContent() {
    const text = document.getElementById('jsonModalContent').innerText || document.getElementById('jsonModalContent').textContent;
    navigator.clipboard.writeText(text).then(() => {
        showToast('JSON copied to clipboard!', 'success');
    });
}

// Lightbox
function openImageModal(url) {
    document.getElementById('lightboxImg').src = url;
    openModal('imageModal');
}

// Submit a hidden DELETE form to a given action URL.
function submitDeleteForm(action) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = action;
    form.innerHTML = `
        <input type="hidden" name="_token" value="${CSRF_TOKEN}">
        <input type="hidden" name="_method" value="DELETE">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Delete Confirmation — single SweetAlert for Video Learning records
function confirmDeleteVideo(id, title) {
    Swal.fire({
        title: 'Delete this video?',
        html: `Are you sure you want to delete <b>"${title || 'this item'}"</b>?<br>This will also remove its video and thumbnail files.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            submitDeleteForm(`{{ url('/video-learning-words') }}/${id}`);
        }
    });
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

// ── Set Index: per-category video reorder popup ──
const CATEGORY_VIDEOS_URL = "{{ url('/video-learning-words/category') }}"; // + /{id}/videos
let draggedVReorderItem = null;

function openVideoReorderModal() {
    // reset
    document.getElementById('reorderCategorySelect').value = '';
    document.getElementById('reorderVideoListWrap').innerHTML =
        '<div style="text-align:center; color:var(--text-muted); font-size:13px; padding:24px 0;">Choose a category to load its videos.</div>';
    document.getElementById('saveVideoReorderBtn').disabled = true;
    openModal('videoReorderModal');
}

function loadCategoryVideosForReorder(categoryId) {
    const wrap = document.getElementById('reorderVideoListWrap');
    const saveBtn = document.getElementById('saveVideoReorderBtn');
    if (!categoryId) {
        wrap.innerHTML = '<div style="text-align:center; color:var(--text-muted); font-size:13px; padding:24px 0;">Choose a category to load its videos.</div>';
        saveBtn.disabled = true;
        return;
    }

    wrap.innerHTML = '<div style="text-align:center; color:var(--text-muted); font-size:13px; padding:24px 0;">Loading…</div>';

    fetch(`${CATEGORY_VIDEOS_URL}/${categoryId}/videos`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const videos = (data && data.data) ? data.data : [];
            if (!videos.length) {
                wrap.innerHTML = '<div style="text-align:center; color:var(--text-muted); font-size:13px; padding:24px 0;">No videos in this category.</div>';
                saveBtn.disabled = true;
                return;
            }
            const ph = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='30' height='44'><rect width='100%' height='100%' fill='%231e293b'/></svg>";
            const items = videos.map((v, i) => `
                <li class="vreorder-item" draggable="true" data-id="${v.id}">
                    <span class="vreorder-grip"><svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg></span>
                    <span class="vreorder-seq">${i + 1}</span>
                    <span class="vreorder-thumb"><img src="${v.thumbnail_url || ph}" onerror="this.src='${ph}'" alt=""></span>
                    <span class="vreorder-name">${(v.video_name || v.title || 'Untitled').replace(/</g,'&lt;')}</span>
                    ${(v.episode_no !== null && v.episode_no !== undefined) ? `<span class="vreorder-ep">Ep ${v.episode_no}</span>` : ''}
                    <span class="vreorder-badge ${v.is_visible ? 'status-badge-visible' : 'status-badge-hidden'}">${v.is_visible ? 'Show' : 'Hide'}</span>
                </li>`).join('');
            const note = data.limited
                ? `<div style="font-size:12px; color:#b45309; background:#fef3c7; border-radius:8px; padding:8px 10px; margin-bottom:10px;">Showing the first ${data.limit} of ${data.total} videos. Reordering applies to these.</div>`
                : '';
            wrap.innerHTML = note + `<ul class="vreorder-list" id="vreorderList">${items}</ul>`;
            saveBtn.disabled = false;
            initVReorderDragDrop();
        })
        .catch(() => {
            wrap.innerHTML = '<div style="text-align:center; color:#ef4444; font-size:13px; padding:24px 0;">Failed to load videos.</div>';
            saveBtn.disabled = true;
        });
}

function refreshVReorderSeq() {
    document.querySelectorAll('#vreorderList .vreorder-item').forEach((li, idx) => {
        const seq = li.querySelector('.vreorder-seq');
        if (seq) seq.textContent = idx + 1;
    });
}

function initVReorderDragDrop() {
    const list = document.getElementById('vreorderList');
    if (!list) return;
    list.querySelectorAll('.vreorder-item').forEach(item => {
        item.addEventListener('dragstart', () => { draggedVReorderItem = item; item.classList.add('dragging'); });
        item.addEventListener('dragend', () => {
            item.classList.remove('dragging');
            draggedVReorderItem = null;
            list.querySelectorAll('.vreorder-item').forEach(i => i.classList.remove('drag-over'));
            refreshVReorderSeq();
        });
        item.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (!draggedVReorderItem || draggedVReorderItem === item) return;
            const rect = item.getBoundingClientRect();
            const after = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
            list.insertBefore(draggedVReorderItem, after ? item.nextSibling : item);
        });
    });
}

function saveVideoReorder() {
    const btn = document.getElementById('saveVideoReorderBtn');
    const ids = Array.from(document.querySelectorAll('#vreorderList .vreorder-item')).map(i => parseInt(i.dataset.id, 10));
    if (!ids.length) return;

    btn.disabled = true;
    btn.style.opacity = '0.7';

    fetch(REORDER_URL, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ ordered_ids: ids })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.style.opacity = '1';
        if (data.success) {
            showToast('Category video sequence saved!', 'success');
            closeModal('videoReorderModal');
            loadPage(1); // refresh the main table
        } else {
            showToast('Could not save sequence.', 'error');
        }
    })
    .catch(() => { btn.disabled = false; btn.style.opacity = '1'; showToast('Network error while saving sequence.', 'error'); });
}
</script>
@endsection
