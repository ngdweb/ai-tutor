<!-- Main Table Card (AJAX-refreshable partial) -->
<div class="table-container">
    @if($items->count() > 0)
    <table class="data-table" id="sortableTable">
        <thead>
            <tr>
                <th style="width: 38px; text-align: center;" title="Drag rows to reorder">↕</th>
                <th style="width: 50px;">#</th>
                <th style="width: 95px;">Thumbnail</th>
                <th>Video File</th>
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
                        <button class="action-btn del-btn" onclick="openDeleteModal({{ $item->id }}, '{{ addslashes($item->video_name ?: basename($item->video_path)) }}')" title="Delete Video Word">
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
