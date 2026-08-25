@extends('layouts.admin')

@php
    $isEdit = isset($record);
    $pageTitle = $isEdit ? 'Edit Video Learning' : 'Add Video Learning Record(s)';
@endphp

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('styles')
<style>
    /* ── Form Page Header ── */
    .form-header-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
    }
    .header-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    .header-breadcrumb a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }
    .form-header-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--text);
        letter-spacing: -0.3px;
    }

    /* ── Record Cards List ── */
    .records-container {
        display: flex;
        flex-direction: column;
        gap: 24px;
        margin-bottom: 24px;
    }

    .record-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
        animation: cardFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .record-card.dragging {
        opacity: 0.45;
        transform: scale(0.98);
        border: 2px dashed var(--primary);
    }
    .record-card.drag-over {
        border-top: 3px solid var(--primary);
    }
    @keyframes cardFadeIn {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .record-card:hover {
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    }

    .record-card-header {
        background: var(--surface2);
        padding: 12px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .record-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .drag-handle {
        cursor: grab;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        border-radius: 6px;
        color: var(--text-muted);
        transition: all 0.15s;
    }
    .drag-handle:hover {
        background: #e2e8f0;
        color: var(--text);
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    .record-badge-num {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13.5px;
        font-weight: 700;
        color: var(--text);
    }
    .record-pill {
        background: #ede9fe;
        color: #6d28d9;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 20px;
    }
    .btn-remove-record {
        background: #fee2e2;
        color: #ef4444;
        border: 1px solid #fecaca;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s;
    }
    .btn-remove-record:hover {
        background: #fca5a5;
        color: #b91c1c;
    }

    .record-card-body {
        padding: 22px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .col-span-2 {
        grid-column: span 2;
    }

    /* ── Form Controls ── */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .form-label .optional {
        font-size: 11.5px;
        color: var(--text-muted);
        font-weight: normal;
    }
    .form-input, .form-textarea {
        width: 100%;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--surface);
        font-size: 13.5px;
        color: var(--text);
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        font-family: inherit;
    }
    .form-textarea {
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 12.5px;
        line-height: 1.45;
        resize: vertical;
        min-height: 135px;
    }
    .form-input:focus, .form-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79,70,229,0.12);
    }

    /* ── File Upload Dropzones ── */
    .file-dropzone {
        border: 2px dashed var(--border);
        border-radius: 12px;
        padding: 16px;
        text-align: center;
        background: var(--surface2);
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    .file-dropzone:hover {
        border-color: var(--primary);
        background: #f5f3ff;
    }
    .file-dropzone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    .dropzone-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        pointer-events: none;
    }
    .dropzone-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #ede9fe;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .dropzone-text {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text);
    }
    .dropzone-hint {
        font-size: 11px;
        color: var(--text-muted);
    }

    .preview-box {
        margin-top: 10px;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
        background: #0f172a;
        height: 260px;
        aspect-ratio: 9 / 16;
        display: none;
        position: relative;
        margin-left: auto;
        margin-right: auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    .preview-box video, .preview-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        border-radius: 12px;
    }
    .preview-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: rgba(15,23,42,0.85);
        backdrop-filter: blur(4px);
        color: #fff;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 10.5px;
        font-weight: 600;
        z-index: 2;
    }

    /* ── Switch Toggle ── */
    .switch-toggle {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 10px;
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

    /* ── Buttons ── */
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
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
    .btn-add-more {
        background: #f0fdf4;
        border: 2px dashed #86efac;
        color: #166534;
        width: 100%;
        padding: 16px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-add-more:hover {
        background: #dcfce7;
        border-color: #4ade80;
        transform: translateY(-1px);
    }

    .form-footer-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 16px 22px;
    }

    /* ── JSON Tools Bar ── */
    .json-tools {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .btn-tool {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: var(--surface2);
        color: var(--text);
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-tool:hover {
        background: #e2e8f0;
    }

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

    @media (max-width: 768px) {
        .record-card-body {
            grid-template-columns: 1fr;
        }
        .col-span-2 {
            grid-column: span 1;
        }
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
    @if(session('error') || $errors->any())
        <div class="toast error" data-auto-dismiss="5000">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') ?? $errors->first() }}</span>
        </div>
    @endif
</div>

<!-- Form Header Bar -->
<div class="form-header-bar">
    <div>
        <div class="header-breadcrumb">
            <a href="{{ route('video-learning.index') }}">Video Learning</a>
            <span>/</span>
            <span>{{ $isEdit ? 'Edit Record' : 'Add New Record(s)' }}</span>
        </div>
        <h2 class="form-header-title">{{ $pageTitle }}</h2>
    </div>

    <a href="{{ route('video-learning.index') }}" class="btn-action btn-outline">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to Listing
    </a>
</div>

<!-- Main Submission Form -->
<form action="{{ $isEdit ? route('video-learning.update', $record->id) : route('video-learning.store') }}" method="POST" enctype="multipart/form-data" id="batchVideoForm">
    @csrf

    <div class="records-container" id="recordsContainer">

        @if($isEdit)
        <!-- EDIT EXISTING RECORD CARD (3 Fields: Video, Thumbnail, JSON + Visibility) -->
        <div class="record-card" id="record-card-0" draggable="true">
            <input type="hidden" name="records[0][id]" value="{{ $record->id }}">
            <input type="hidden" name="records[0][auto_thumbnail_base64]" id="auto_thumb_0">

            <div class="record-card-header">
                <div class="record-header-left">
                    <div class="drag-handle" title="Drag to reorder card sequence">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                            <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                            <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                        </svg>
                    </div>
                    <div class="record-badge-num">
                        <span class="record-pill">Record #1 (Editing)</span>
                        <span style="font-weight: 600; font-size: 13px; color: var(--text);">{{ Str::limit($record->video_name ?: basename($record->video_path), 35) }}</span>
                    </div>
                </div>
            </div>

            <div class="record-card-body">
                <!-- 1. Video File -->
                <div class="form-group">
                    <label class="form-label">
                        <span>1. Video File</span>
                        <span class="optional">Leave blank to keep existing</span>
                    </label>
                    <div class="file-dropzone">
                        <input type="file" name="records[0][video]" accept="video/mp4,video/webm,video/ogg,video/quicktime" onchange="handleRowVideoSelection(this, 0)">
                        <div class="dropzone-content">
                            <div class="dropzone-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                            </div>
                            <span class="dropzone-text">Click or drag to replace video</span>
                            <span class="dropzone-hint">Current: {{ Str::limit($record->video_name ?: basename($record->video_path), 30) }}</span>
                        </div>
                    </div>
                    <div class="preview-box" id="video_preview_box_0" style="display:block;">
                        <span class="preview-badge">Current Video</span>
                        <video id="video_element_0" src="{{ $record->video_url }}" controls muted playsinline></video>
                    </div>
                </div>

                <!-- 2. Thumbnail Image -->
                <div class="form-group">
                    <label class="form-label">
                        <span>2. Thumbnail Image</span>
                        <span class="optional">Leave blank to keep existing</span>
                    </label>
                    <div class="file-dropzone">
                        <input type="file" name="records[0][thumbnail]" accept="image/png,image/jpeg,image/webp,image/jpg" onchange="handleRowImageSelection(this, 0)">
                        <div class="dropzone-content">
                            <div class="dropzone-icon" style="background:#dcfce7; color:#16a34a;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                            <span class="dropzone-text">Click or drag to replace thumbnail</span>
                            <span class="dropzone-hint">JPG, PNG, WebP (Canvas fallback)</span>
                        </div>
                    </div>
                    <div class="preview-box" id="thumb_preview_box_0" style="display:block;">
                        <span class="preview-badge" id="thumb_badge_0">Current Thumbnail</span>
                        <img id="thumb_img_0" src="{{ $record->thumbnail_url }}" alt="Thumbnail">
                    </div>
                </div>

                <!-- 3. JSON Data -->
                <div class="form-group col-span-2">
                    <div class="form-label">
                        <span>3. JSON Content <strong style="color:#ef4444;">*</strong></span>
                        <div class="json-tools">
                            <button type="button" class="btn-tool" onclick="insertRowTemplate(0)">Template</button>
                            <button type="button" class="btn-tool" onclick="prettifyRowJson(0)">Prettify JSON</button>
                        </div>
                    </div>
                    <textarea name="records[0][json_data]" id="json_data_0" class="form-textarea" required>{{ $record->json_data }}</textarea>
                </div>

                <!-- Visibility Toggle -->
                <div class="form-group col-span-2">
                    <label class="switch-toggle">
                        <input type="hidden" name="records[0][is_visible]" value="0">
                        <input type="checkbox" name="records[0][is_visible]" value="1" {{ $record->is_visible ? 'checked' : '' }}>
                        <span class="switch-track"><span class="switch-thumb"></span></span>
                        <span style="font-size: 13.5px; font-weight: 600; color: var(--text);">Show in API Response</span>
                    </label>
                </div>
            </div>
        </div>

        @else
        <!-- INITIAL CREATE RECORD CARD #1 (3 Fields: Video, Thumbnail, JSON + Visibility) -->
        <div class="record-card" id="record-card-0" draggable="true">
            <input type="hidden" name="records[0][auto_thumbnail_base64]" id="auto_thumb_0">

            <div class="record-card-header">
                <div class="record-header-left">
                    <div class="drag-handle" title="Drag to reorder card sequence">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                            <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                            <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                        </svg>
                    </div>
                    <div class="record-badge-num">
                        <span class="record-pill">Record #1</span>
                        <span style="font-weight: 600; font-size: 13px; color: var(--text);">New Video Learning Item</span>
                    </div>
                </div>
            </div>

            <div class="record-card-body">
                <!-- 1. Video File (Required) -->
                <div class="form-group">
                    <label class="form-label">
                        <span>1. Video File <strong style="color:#ef4444;">*</strong></span>
                        <span class="optional">MP4, WebM, MOV (Max 200MB)</span>
                    </label>
                    <div class="file-dropzone">
                        <input type="file" name="records[0][video]" accept="video/mp4,video/webm,video/ogg,video/quicktime" required onchange="handleRowVideoSelection(this, 0)">
                        <div class="dropzone-content">
                            <div class="dropzone-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                            </div>
                            <span class="dropzone-text">Click or drag video file here</span>
                            <span class="dropzone-hint">Canvas will auto-capture frame if no thumb</span>
                        </div>
                    </div>
                    <div class="preview-box" id="video_preview_box_0">
                        <span class="preview-badge">Selected Video</span>
                        <video id="video_element_0" controls muted playsinline></video>
                    </div>
                </div>

                <!-- 2. Thumbnail Image (Optional) -->
                <div class="form-group">
                    <label class="form-label">
                        <span>2. Thumbnail Image</span>
                        <span class="optional">Canvas auto-capture if empty</span>
                    </label>
                    <div class="file-dropzone">
                        <input type="file" name="records[0][thumbnail]" accept="image/png,image/jpeg,image/webp,image/jpg" onchange="handleRowImageSelection(this, 0)">
                        <div class="dropzone-content">
                            <div class="dropzone-icon" style="background:#dcfce7; color:#16a34a;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                            <span class="dropzone-text">Upload Custom Thumbnail (Optional)</span>
                            <span class="dropzone-hint">JPG, PNG, WebP</span>
                        </div>
                    </div>
                    <div class="preview-box" id="thumb_preview_box_0">
                        <span class="preview-badge" id="thumb_badge_0">Thumbnail Preview</span>
                        <img id="thumb_img_0" src="" alt="Thumbnail preview">
                    </div>
                </div>

                <!-- 3. JSON Data -->
                <div class="form-group col-span-2">
                    <div class="form-label">
                        <span>3. JSON Content <strong style="color:#ef4444;">*</strong></span>
                        <div class="json-tools">
                            <button type="button" class="btn-tool" onclick="insertRowTemplate(0)">Template</button>
                            <button type="button" class="btn-tool" onclick="prettifyRowJson(0)">Prettify JSON</button>
                        </div>
                    </div>
                    <textarea name="records[0][json_data]" id="json_data_0" class="form-textarea" placeholder='{\n  "word": "Apple",\n  "phonetic": "/ˈæp.əl/",\n  "meaning": "A round fruit",\n  "timestamps": [0, 2.5]\n}' required>{}</textarea>
                </div>

                <!-- Visibility Toggle -->
                <div class="form-group col-span-2">
                    <label class="switch-toggle">
                        <input type="hidden" name="records[0][is_visible]" value="0">
                        <input type="checkbox" name="records[0][is_visible]" value="1" checked>
                        <span class="switch-track"><span class="switch-thumb"></span></span>
                        <span style="font-size: 13.5px; font-weight: 600; color: var(--text);">Show in API Response</span>
                    </label>
                </div>
            </div>
        </div>
        @endif

    </div>

    <!-- "Add More Record" Button -->
    <button type="button" class="btn-add-more" onclick="addNewRecordCard()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        <span>Add More Record</span>
    </button>

    <!-- Submit Footer -->
    <div class="form-footer-actions">
        <a href="{{ route('video-learning.index') }}" class="btn-action btn-outline">Cancel</a>
        <button type="submit" class="btn-action btn-primary" id="saveAllSubmitBtn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ $isEdit ? 'Save Changes' : 'Save All Records' }}</span>
        </button>
    </div>
</form>

<!-- Hidden Canvas for Video Frame Capture -->
<canvas id="hiddenCanvas" style="display:none;"></canvas>

@endsection

@section('scripts')
<script>
let recordIndexCounter = 1;

// Add New Record Card dynamically
function addNewRecordCard() {
    const idx = recordIndexCounter++;
    const container = document.getElementById('recordsContainer');
    const totalCurrentCards = container.querySelectorAll('.record-card').length + 1;

    const cardHtml = `
        <div class="record-card" id="record-card-${idx}" draggable="true">
            <input type="hidden" name="records[${idx}][auto_thumbnail_base64]" id="auto_thumb_${idx}">

            <div class="record-card-header">
                <div class="record-header-left">
                    <div class="drag-handle" title="Drag to reorder card sequence">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                            <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                            <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
                        </svg>
                    </div>
                    <div class="record-badge-num">
                        <span class="record-pill">Record #${totalCurrentCards}</span>
                        <span style="font-weight: 600; font-size: 13px; color: var(--text);">Additional Video Learning Item</span>
                    </div>
                </div>
                <button type="button" class="btn-remove-record" onclick="removeRecordCard(${idx})">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    Remove
                </button>
            </div>

            <div class="record-card-body">
                <!-- 1. Video File (Required) -->
                <div class="form-group">
                    <label class="form-label">
                        <span>1. Video File <strong style="color:#ef4444;">*</strong></span>
                        <span class="optional">MP4, WebM, MOV (Max 200MB)</span>
                    </label>
                    <div class="file-dropzone">
                        <input type="file" name="records[${idx}][video]" accept="video/mp4,video/webm,video/ogg,video/quicktime" required onchange="handleRowVideoSelection(this, ${idx})">
                        <div class="dropzone-content">
                            <div class="dropzone-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                            </div>
                            <span class="dropzone-text">Click or drag video file here</span>
                            <span class="dropzone-hint">Canvas will auto-capture frame if no thumb</span>
                        </div>
                    </div>
                    <div class="preview-box" id="video_preview_box_${idx}">
                        <span class="preview-badge">Selected Video</span>
                        <video id="video_element_${idx}" controls muted playsinline></video>
                    </div>
                </div>

                <!-- 2. Thumbnail Image -->
                <div class="form-group">
                    <label class="form-label">
                        <span>2. Thumbnail Image</span>
                        <span class="optional">Canvas auto-capture if empty</span>
                    </label>
                    <div class="file-dropzone">
                        <input type="file" name="records[${idx}][thumbnail]" accept="image/png,image/jpeg,image/webp,image/jpg" onchange="handleRowImageSelection(this, ${idx})">
                        <div class="dropzone-content">
                            <div class="dropzone-icon" style="background:#dcfce7; color:#16a34a;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            </div>
                            <span class="dropzone-text">Upload Custom Thumbnail (Optional)</span>
                            <span class="dropzone-hint">JPG, PNG, WebP</span>
                        </div>
                    </div>
                    <div class="preview-box" id="thumb_preview_box_${idx}">
                        <span class="preview-badge" id="thumb_badge_${idx}">Thumbnail Preview</span>
                        <img id="thumb_img_${idx}" src="" alt="Thumbnail preview">
                    </div>
                </div>

                <!-- 3. JSON Data -->
                <div class="form-group col-span-2">
                    <div class="form-label">
                        <span>3. JSON Content <strong style="color:#ef4444;">*</strong></span>
                        <div class="json-tools">
                            <button type="button" class="btn-tool" onclick="insertRowTemplate(${idx})">Template</button>
                            <button type="button" class="btn-tool" onclick="prettifyRowJson(${idx})">Prettify JSON</button>
                        </div>
                    </div>
                    <textarea name="records[${idx}][json_data]" id="json_data_${idx}" class="form-textarea" required>{}</textarea>
                </div>

                <!-- Visibility Toggle -->
                <div class="form-group col-span-2">
                    <label class="switch-toggle">
                        <input type="hidden" name="records[${idx}][is_visible]" value="0">
                        <input type="checkbox" name="records[${idx}][is_visible]" value="1" checked>
                        <span class="switch-track"><span class="switch-thumb"></span></span>
                        <span style="font-size: 13.5px; font-weight: 600; color: var(--text);">Show in API Response</span>
                    </label>
                </div>
            </div>
        </div>
    `;

    const div = document.createElement('div');
    div.innerHTML = cardHtml.trim();
    const newCardEl = div.firstChild;
    container.appendChild(newCardEl);

    attachDragListeners(newCardEl);

    newCardEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    updateCardNumbers();
}

// Remove Record Card
function removeRecordCard(idx) {
    const card = document.getElementById(`record-card-${idx}`);
    if (card) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(-10px)';
        card.style.transition = 'all 0.2s';
        setTimeout(() => {
            card.remove();
            updateCardNumbers();
        }, 200);
    }
}

// Update Card Number Badges after reordering or removing
function updateCardNumbers() {
    const cards = document.querySelectorAll('.records-container .record-card');
    cards.forEach((card, index) => {
        const pill = card.querySelector('.record-pill');
        if (pill) {
            pill.textContent = `Record #${index + 1}` + (card.querySelector('input[name*="[id]"]') ? ' (Editing)' : '');
        }
    });
}

// Drag and Drop Cards Reordering
let draggedCard = null;

function attachDragListeners(card) {
    card.addEventListener('dragstart', (e) => {
        draggedCard = card;
        card.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });

    card.addEventListener('dragend', () => {
        if (draggedCard) {
            draggedCard.classList.remove('dragging');
            draggedCard = null;
        }
        document.querySelectorAll('.record-card').forEach(c => c.classList.remove('drag-over'));
        updateCardNumbers();
    });

    card.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        if (!draggedCard || draggedCard === card) return;

        const rect = card.getBoundingClientRect();
        const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
        const container = document.getElementById('recordsContainer');
        container.insertBefore(draggedCard, next ? card.nextSibling : card);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.records-container .record-card').forEach(attachDragListeners);
});

// Video Selection & Canvas Frame Capture per Row
function handleRowVideoSelection(input, idx) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const videoEl = document.getElementById(`video_element_${idx}`);
        const previewBox = document.getElementById(`video_preview_box_${idx}`);
        const canvas = document.getElementById('hiddenCanvas');

        const url = URL.createObjectURL(file);
        videoEl.src = url;
        previewBox.style.display = 'block';

        videoEl.onloadeddata = function() {
            videoEl.currentTime = 0.5;
        };
        videoEl.onseeked = function() {
            try {
                canvas.width = videoEl.videoWidth || 640;
                canvas.height = videoEl.videoHeight || 360;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
                const hiddenInput = document.getElementById(`auto_thumb_${idx}`);
                if (hiddenInput) hiddenInput.value = dataUrl;

                const imgEl = document.getElementById(`thumb_img_${idx}`);
                const badgeEl = document.getElementById(`thumb_badge_${idx}`);
                if (imgEl && (!imgEl.dataset.customFile || imgEl.dataset.customFile === 'false')) {
                    imgEl.src = dataUrl;
                    imgEl.parentElement.style.display = 'block';
                    if (badgeEl) badgeEl.textContent = 'Auto-Captured Frame (Canvas)';
                }
            } catch (e) {
                console.warn('Canvas frame capture fallback', e);
            }
        };
    }
}

// Custom Thumbnail Selection per Row
function handleRowImageSelection(input, idx) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(`thumb_img_${idx}`);
            const box = document.getElementById(`thumb_preview_box_${idx}`);
            const badge = document.getElementById(`thumb_badge_${idx}`);
            if (img) {
                img.src = e.target.result;
                img.dataset.customFile = 'true';
            }
            if (box) box.style.display = 'block';
            if (badge) badge.textContent = 'Custom Uploaded Thumbnail';
        };
        reader.readAsDataURL(file);
    }
}

// JSON Tools per row
function insertRowTemplate(idx) {
    const template = {
        "word": "Example",
        "phonetic": "/ɪɡˈzæm.pəl/",
        "definition": "A representative form or pattern",
        "part_of_speech": "noun",
        "timestamps": [
            { "start": 0.0, "end": 2.5, "text": "Pronunciation" },
            { "start": 2.6, "end": 5.0, "text": "Usage in Sentence" }
        ],
        "example_sentence": "This is an example sentence for video learning."
    };
    const textarea = document.getElementById(`json_data_${idx}`);
    if (textarea) textarea.value = JSON.stringify(template, null, 2);
}

function prettifyRowJson(idx) {
    const el = document.getElementById(`json_data_${idx}`);
    if (!el) return;
    try {
        const parsed = JSON.parse(el.value);
        el.value = JSON.stringify(parsed, null, 2);
    } catch (e) {
        alert('Invalid JSON syntax: ' + e.message);
    }
}

// Prettify on initial load if editing & auto-dismiss flash toasts after 5 seconds
document.addEventListener('DOMContentLoaded', () => {
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => {
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(16px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    });

    const initialJson = document.getElementById('json_data_0');
    if (initialJson && initialJson.value) {
        try {
            const parsed = JSON.parse(initialJson.value);
            initialJson.value = JSON.stringify(parsed, null, 2);
        } catch (e) {}
    }
});
</script>
@endsection
