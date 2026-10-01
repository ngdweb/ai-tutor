@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Categories')

@section('styles')
<style>
    .module-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
    .module-title-group h2 { font-size: 20px; font-weight: 700; color: var(--text); letter-spacing: -0.3px; }
    .module-title-group p { font-size: 13px; color: var(--text-muted); margin-top: 3px; }

    .stats-pills { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .stat-pill { display: flex; align-items: center; gap: 8px; background: var(--surface); border: 1px solid var(--border); padding: 6px 14px; border-radius: 30px; font-size: 12.5px; font-weight: 600; color: var(--text); box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
    .stat-pill .dot { width: 8px; height: 8px; border-radius: 50%; }
    .dot-indigo { background: #4f46e5; } .dot-green { background: #16a34a; } .dot-gray { background: #94a3b8; }

    .toolbar-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 16px 20px; margin-bottom: 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    .toolbar-left { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; flex: 1; min-width: 280px; }
    .search-input-wrap { position: relative; flex: 1; max-width: 380px; min-width: 200px; }
    .search-input-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 17px; height: 17px; color: var(--text-muted); pointer-events: none; }
    .search-input { width: 100%; padding: 9px 14px 9px 38px; border-radius: 10px; border: 1px solid var(--border); background: var(--surface2); font-size: 13.5px; color: var(--text); outline: none; transition: all 0.2s ease; }
    .search-input:focus { border-color: var(--primary); background: var(--surface); box-shadow: 0 0 0 3px rgba(79,70,229,0.12); }
    .select-filter { padding: 9px 14px; border-radius: 10px; border: 1px solid var(--border); background: var(--surface2); font-size: 13.5px; color: var(--text); outline: none; cursor: pointer; }
    .select-filter:focus { border-color: var(--primary); }

    .btn-action { display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 10px; font-size: 13.5px; font-weight: 600; cursor: pointer; border: none; transition: all 0.18s ease; text-decoration: none; }
    .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 2px 8px rgba(79,70,229,0.28); }
    .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(79,70,229,0.35); }
    .btn-outline { background: var(--surface); border: 1px solid var(--border); color: var(--text); }
    .btn-outline:hover { background: var(--surface2); }

    .table-container { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    .data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px; }
    .data-table th { background: var(--surface2); padding: 13px 16px; font-weight: 600; color: var(--text-muted); border-bottom: 1px solid var(--border); text-transform: uppercase; font-size: 11px; letter-spacing: 0.8px; }
    .data-table td { padding: 14px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; color: var(--text); }
    .data-table tr:last-child td { border-bottom: none; }
    .data-table tbody tr:hover td { background: #fcfdfe; }

    .cat-thumb { width: 52px; height: 52px; border-radius: 10px; overflow: hidden; background: #0f172a; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
    .cat-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .cat-thumb-placeholder { color: #94a3b8; }
    .cat-name { font-size: 14px; font-weight: 600; color: var(--text); }
    .count-pill { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 12px; background: #eef2ff; color: #4338ca; font-size: 12px; font-weight: 700; }

    .switch-toggle { position: relative; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; }
    .switch-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
    .switch-track { width: 44px; height: 24px; background-color: #cbd5e1; border-radius: 30px; transition: background-color 0.25s ease; position: relative; }
    .switch-thumb { position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background: #fff; border-radius: 50%; transition: transform 0.25s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.25); }
    .switch-toggle input:checked + .switch-track { background-color: #10b981; }
    .switch-toggle input:checked + .switch-track .switch-thumb { transform: translateX(20px); }
    .status-text-badge { font-size: 11.5px; font-weight: 700; padding: 3px 8px; border-radius: 12px; }
    .status-badge-visible { background: #dcfce7; color: #15803d; }
    .status-badge-hidden { background: #f1f5f9; color: #64748b; }

    .action-btn-group { display: flex; align-items: center; gap: 6px; }
    .action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text-muted); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s ease; text-decoration: none; }
    .action-btn:hover { background: var(--surface2); color: var(--text); }
    .action-btn.edit-btn:hover { background: #ede9fe; color: #6d28d9; border-color: #ddd6fe; }
    .action-btn.del-btn:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

    .empty-state { text-align: center; padding: 56px 20px; }
    .empty-icon { width: 58px; height: 58px; border-radius: 16px; background: #ede9fe; color: #6d28d9; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; }
    .empty-state h3 { font-size: 16px; font-weight: 700; color: var(--text); }
    .empty-state p { font-size: 13px; color: var(--text-muted); margin-top: 4px; margin-bottom: 18px; }

    .pagination-bar { padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border); background: var(--surface); flex-wrap: wrap; gap: 10px; }
    .ajax-pagination { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
    .ajax-pagination .page-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; color: var(--text-muted); background: transparent; border: 1px solid var(--border); transition: background 0.15s, color 0.15s, border-color 0.15s; user-select: none; }
    .ajax-pagination .page-btn:hover:not(.disabled):not(.active) { background: var(--surface2); color: var(--text); border-color: var(--primary); }
    .ajax-pagination .page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); cursor: default; pointer-events: none; }
    .ajax-pagination .page-btn.disabled { opacity: 0.38; cursor: default; pointer-events: none; }
    #tableWrapper { position: relative; min-height: 120px; }
    #tableWrapper.loading::after { content: ''; position: absolute; inset: 0; background: rgba(255,255,255,0.45); border-radius: 12px; z-index: 10; }
    #tableWrapper.loading::before { content: ''; position: absolute; top: 50%; left: 50%; width: 34px; height: 34px; margin: -17px 0 0 -17px; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.6s linear infinite; z-index: 11; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Modal */
    .modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,0.55); backdrop-filter: blur(4px); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 20px; }
    .modal-backdrop.active { display: flex; }
    .modal-card { background: var(--surface); border: 1px solid var(--border); border-radius: 18px; width: 100%; max-width: 440px; box-shadow: 0 20px 40px rgba(0,0,0,0.18); overflow: hidden; }
    .modal-header { padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; }
    .modal-close-btn { background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 4px; border-radius: 7px; display: flex; }
    .modal-close-btn:hover { background: var(--surface2); }
    .modal-body { padding: 10px 24px; font-size: 14px; color: var(--text); }
    .modal-footer { padding: 18px 24px; display: flex; justify-content: flex-end; gap: 10px; }
    .btn-danger { background: #ef4444; color: #fff; }
    .btn-danger:hover { background: #dc2626; }

    /* Reorder (Set Index) list */
    .reorder-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
    .reorder-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; background: var(--surface2); cursor: grab; transition: background 0.15s, box-shadow 0.15s, opacity 0.15s; }
    .reorder-item:hover { background: #f5f3ff; border-color: #ddd6fe; }
    .reorder-item.dragging { opacity: 0.45; background: #ede9fe; cursor: grabbing; }
    .reorder-item.drag-over { border-top: 2px solid var(--primary); }
    .reorder-grip { color: var(--text-muted); display: flex; }
    .reorder-seq { min-width: 24px; height: 24px; border-radius: 6px; background: #eef2ff; color: #4338ca; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
    .reorder-thumb { width: 34px; height: 34px; border-radius: 8px; overflow: hidden; background: #0f172a; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid var(--border); }
    .reorder-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .reorder-name { flex: 1; font-size: 14px; font-weight: 600; color: var(--text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .reorder-badge { font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 12px; }

    /* Toast */
    #toastContainer { position: fixed; bottom: 24px; right: 24px; z-index: 2000; display: flex; flex-direction: column; gap: 10px; }
    .toast { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 12px; font-size: 13.5px; font-weight: 600; color: #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.18); min-width: 240px; }
    .toast.success { background: #16a34a; } .toast.error { background: #ef4444; }
    .toast svg { width: 18px; height: 18px; flex-shrink: 0; }

    @media (max-width: 640px) { .data-table th:nth-child(4), .data-table td:nth-child(4) { display: none; } }
</style>
@endsection

@section('content')
<div id="toastContainer"></div>

@if(session('success'))
    <div class="toast success" style="position: fixed; bottom: 24px; right: 24px; z-index: 2000;" data-auto-dismiss="5000">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div class="toast error" style="position: fixed; bottom: 24px; right: 24px; z-index: 2000;" data-auto-dismiss="5000">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

<!-- Header -->
<div class="module-header">
    <div class="module-title-group">
        <h2>Categories</h2>
        <p>Manage categories for the video learning module. Toggle On/Off to control API visibility.</p>
    </div>
    <div class="stats-pills">
        <div class="stat-pill"><span class="dot dot-indigo"></span><span>Total: <strong>{{ $stats['total'] }}</strong></span></div>
        <div class="stat-pill"><span class="dot dot-green"></span><span>Active (On): <strong>{{ $stats['active'] }}</strong></span></div>
        <div class="stat-pill"><span class="dot dot-gray"></span><span>Inactive (Off): <strong>{{ $stats['inactive'] }}</strong></span></div>
    </div>
</div>

<!-- Toolbar -->
<div class="toolbar-card">
    <form method="GET" action="{{ route('categories.index') }}" class="toolbar-left" id="filterForm">
        <div class="search-input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" class="search-input" placeholder="Search by category name..." value="{{ request('search') }}">
        </div>
        <select name="status" class="select-filter">
            <option value="">All Statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>On (Active)</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Off (Inactive)</option>
        </select>
        @if(request('search') || request('status'))
            <a href="{{ route('categories.index') }}" class="btn-action btn-outline reset-filter-btn" style="padding: 8px 12px;" title="Reset filters">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reset
            </a>
        @endif
    </form>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="btn-action btn-outline" onclick="openReorderModal()" title="Set category sequence / index">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="9" y2="18"/><polyline points="17 10 21 14 17 18"/></svg>
            Set Index
        </button>
        <a href="{{ route('categories.create') }}" class="btn-action btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add New Category
        </a>
    </div>
</div>

<div id="tableWrapper">
    @fragment('categoryTable')
    <div class="table-container">
        @if($items->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th style="width: 80px;">Image</th>
                    <th>Category Name</th>
                    <th style="width: 120px;">Videos</th>
                    <th style="width: 140px;">Status</th>
                    <th style="width: 100px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td style="color: var(--text-muted); font-weight: 500;">
                        {{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}
                    </td>
                    <td>
                        <div class="cat-thumb">
                            @if($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                            @else
                                <span class="cat-thumb-placeholder">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </span>
                            @endif
                        </div>
                    </td>
                    <td><span class="cat-name">{{ $item->name }}</span></td>
                    <td>
                        <span class="count-pill">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                            {{ $item->videos_count }}
                        </span>
                    </td>
                    <td>
                        <label class="switch-toggle" title="Toggle category On/Off">
                            <input type="checkbox" onchange="toggleCategoryStatus({{ $item->id }}, this)" {{ $item->is_active ? 'checked' : '' }}>
                            <span class="switch-track"><span class="switch-thumb"></span></span>
                            <span class="status-text-badge {{ $item->is_active ? 'status-badge-visible' : 'status-badge-hidden' }}" id="cat-status-badge-{{ $item->id }}">
                                {{ $item->is_active ? 'On' : 'Off' }}
                            </span>
                        </label>
                    </td>
                    <td>
                        <div class="action-btn-group" style="justify-content: flex-end;">
                            <a href="{{ route('categories.edit', $item->id) }}" class="action-btn edit-btn" title="Edit Category">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <button class="action-btn del-btn" onclick="confirmDeleteCategory({{ $item->id }}, '{{ addslashes($item->name) }}')" title="Delete Category">
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
                @if($items->onFirstPage())
                    <span class="page-btn disabled">&lsaquo; Prev</span>
                @else
                    <a href="{{ $items->previousPageUrl() }}" class="page-btn ajax-page" data-page="{{ $items->currentPage() - 1 }}">&lsaquo; Prev</a>
                @endif

                @foreach($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                    @if($page == $items->currentPage())
                        <span class="page-btn active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-btn ajax-page" data-page="{{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach

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
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            </div>
            <h3>No categories found</h3>
            <p>Create your first category to organise video learning records.</p>
            <a href="{{ route('categories.create') }}" class="btn-action btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add New Category
            </a>
        </div>
        @endif
    </div>
    @endfragment
</div>

<!-- Reorder (Set Index) Modal -->
<div class="modal-backdrop" id="reorderModal">
    <div class="modal-card" style="max-width: 520px;">
        <div class="modal-header" style="border-bottom: 1px solid var(--border);">
            <div>
                <div style="font-size: 16px; font-weight: 700; color: var(--text);">Set Category Index</div>
                <div style="font-size: 12.5px; color: var(--text-muted); margin-top: 2px;">Drag the rows to set the display order. This sequence is used in the API response.</div>
            </div>
            <button class="modal-close-btn" onclick="closeModal('reorderModal')"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body" style="padding: 16px 20px; max-height: 60vh; overflow-y: auto;">
            <ul class="reorder-list" id="reorderList">
                @foreach($allCategories as $cat)
                <li class="reorder-item" draggable="true" data-id="{{ $cat->id }}">
                    <span class="reorder-grip">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>
                    </span>
                    <span class="reorder-seq">{{ $loop->iteration }}</span>
                    <span class="reorder-thumb">
                        @if($cat->image_url)
                            <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}">
                        @else
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#94a3b8;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        @endif
                    </span>
                    <span class="reorder-name">{{ $cat->name }}</span>
                    <span class="reorder-badge {{ $cat->is_active ? 'status-badge-visible' : 'status-badge-hidden' }}">{{ $cat->is_active ? 'On' : 'Off' }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-action btn-outline" onclick="closeModal('reorderModal')">Close</button>
            <button type="button" class="btn-action btn-primary" id="saveReorderBtn" onclick="saveCategoryReorder()">
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
const LIST_URL = "{{ route('categories.index') }}";

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${type === 'success' ? '<polyline points="20 6 9 17 4 12"/>' : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'}</svg><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 5000);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toast[data-auto-dismiss]').forEach(t => {
        setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity 0.3s'; setTimeout(() => t.remove(), 300); }, 5000);
    });
});

function openModal(id) { document.getElementById(id).classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeModal(id) { document.getElementById(id).classList.remove('active'); document.body.style.overflow = ''; }

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

// Delete Confirmation — TWO SweetAlerts for Category (destructive: removes all its videos)
function confirmDeleteCategory(id, name) {
    const label = name || 'this category';
    // Alert 1
    Swal.fire({
        title: 'Delete this category?',
        html: `Are you sure you want to delete <b>"${label}"</b>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((first) => {
        if (!first.isConfirmed) return;
        // Alert 2 (final confirmation)
        Swal.fire({
            title: 'This cannot be undone!',
            html: `All videos inside <b>"${label}"</b> and their files will be <b>permanently deleted</b>.<br>Do you really want to continue?`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, delete everything',
            cancelButtonText: 'No, keep it',
            reverseButtons: true
        }).then((second) => {
            if (second.isConfirmed) {
                submitDeleteForm(`{{ url('/categories') }}/${id}`);
            }
        });
    });
}

// Toggle Active status (AJAX)
function toggleCategoryStatus(id, checkbox) {
    const badge = document.getElementById(`cat-status-badge-${id}`);
    const original = checkbox.checked;
    fetch(`{{ url('/categories') }}/${id}/toggle-status`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            checkbox.checked = data.is_active;
            badge.className = 'status-text-badge ' + (data.is_active ? 'status-badge-visible' : 'status-badge-hidden');
            badge.textContent = data.is_active ? 'On' : 'Off';
            showToast(data.message, 'success');
        } else {
            checkbox.checked = !original;
            showToast('Could not update status.', 'error');
        }
    })
    .catch(() => { checkbox.checked = !original; showToast('Network error while updating status.', 'error'); });
}

// ── AJAX list loading (search / filter / pagination without route change) ──
const wrapper = document.getElementById('tableWrapper');

function loadPage(page = 1) {
    const filterForm = document.getElementById('filterForm');
    const params = new URLSearchParams();
    const search = filterForm.querySelector('input[name="search"]').value.trim();
    const status = filterForm.querySelector('select[name="status"]').value;
    if (search) params.set('search', search);
    if (status) params.set('status', status);
    if (page > 1) params.set('page', page);

    wrapper.classList.add('loading');
    const url = LIST_URL + (params.toString() ? ('?' + params.toString()) : '');

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            wrapper.innerHTML = html;
            wrapper.classList.remove('loading');
            // Keep the browser URL in sync without reloading / changing route
            window.history.replaceState({}, '', url);
            bindListEvents();
        })
        .catch(() => { wrapper.classList.remove('loading'); showToast('Failed to load categories.', 'error'); });
}

let searchTimer = null;
function bindFilters() {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) return;
    filterForm.addEventListener('submit', e => e.preventDefault());
    const searchInput = filterForm.querySelector('input[name="search"]');
    if (searchInput) searchInput.addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(() => loadPage(1), 400); });
    const statusSelect = filterForm.querySelector('select[name="status"]');
    if (statusSelect) statusSelect.addEventListener('change', () => loadPage(1));

    document.querySelectorAll('.reset-filter-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            searchInput.value = '';
            statusSelect.value = '';
            loadPage(1);
        });
    });
}

function bindListEvents() {
    wrapper.querySelectorAll('.ajax-page').forEach(link => {
        link.addEventListener('click', e => { e.preventDefault(); loadPage(parseInt(link.dataset.page, 10) || 1); });
    });
}

// ── Set Index (drag-and-drop category reorder popup) ──
const REORDER_URL = "{{ route('categories.reorder') }}";
let draggedReorderItem = null;

function openReorderModal() {
    openModal('reorderModal');
    initReorderDragDrop();
}

function refreshReorderSeqNumbers() {
    document.querySelectorAll('#reorderList .reorder-item').forEach((li, idx) => {
        const seq = li.querySelector('.reorder-seq');
        if (seq) seq.textContent = idx + 1;
    });
}

function initReorderDragDrop() {
    const list = document.getElementById('reorderList');
    if (!list) return;

    list.querySelectorAll('.reorder-item').forEach(item => {
        if (item.dataset.dragBound === '1') return;
        item.dataset.dragBound = '1';

        item.addEventListener('dragstart', () => { draggedReorderItem = item; item.classList.add('dragging'); });
        item.addEventListener('dragend', () => {
            item.classList.remove('dragging');
            draggedReorderItem = null;
            list.querySelectorAll('.reorder-item').forEach(i => i.classList.remove('drag-over'));
            refreshReorderSeqNumbers();
        });
        item.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (!draggedReorderItem || draggedReorderItem === item) return;
            const rect = item.getBoundingClientRect();
            const after = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
            list.insertBefore(draggedReorderItem, after ? item.nextSibling : item);
        });
    });
}

function saveCategoryReorder() {
    const btn = document.getElementById('saveReorderBtn');
    const ids = Array.from(document.querySelectorAll('#reorderList .reorder-item')).map(i => parseInt(i.dataset.id, 10));

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
            showToast(data.message || 'Sequence saved!', 'success');
            closeModal('reorderModal');
            loadPage(1); // refresh the list to reflect new order
        } else {
            showToast('Could not save sequence.', 'error');
        }
    })
    .catch(() => { btn.disabled = false; btn.style.opacity = '1'; showToast('Network error while saving sequence.', 'error'); });
}

document.addEventListener('DOMContentLoaded', () => {
    bindFilters();
    bindListEvents();
});
</script>
@endsection
