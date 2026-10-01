@extends('layouts.admin')

@php
    $isEdit = isset($record);
    $pageTitle = $isEdit ? 'Edit Category' : 'Add Category';
@endphp

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('styles')
<style>
    .form-header-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
    .header-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 4px; }
    .header-breadcrumb a { color: var(--primary); text-decoration: none; font-weight: 500; }
    .form-header-title { font-size: 22px; font-weight: 700; color: var(--text); letter-spacing: -0.3px; }

    .form-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.03); overflow: hidden; max-width: 640px; }
    .form-card-body { padding: 26px 28px; }
    .form-group { margin-bottom: 22px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 8px; }
    .form-label .req { color: #ef4444; }
    .form-input { width: 100%; padding: 11px 14px; border-radius: 10px; border: 1px solid var(--border); background: var(--surface2); font-size: 14px; color: var(--text); outline: none; transition: all 0.2s ease; }
    .form-input:focus { border-color: var(--primary); background: var(--surface); box-shadow: 0 0 0 3px rgba(79,70,229,0.12); }
    .form-hint { font-size: 12px; color: var(--text-muted); margin-top: 6px; }
    .error-text { color: #ef4444; font-size: 12.5px; margin-top: 6px; }

    /* Image upload */
    .image-drop { border: 2px dashed var(--border); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.18s; background: var(--surface2); }
    .image-drop:hover { border-color: var(--primary); background: #f5f3ff; }
    .image-drop input { display: none; }
    .image-preview-wrap { display: flex; align-items: center; gap: 16px; }
    .image-preview { width: 90px; height: 90px; border-radius: 12px; object-fit: cover; border: 1px solid var(--border); background: #0f172a; display: none; }
    .image-preview.show { display: block; }

    /* Toggle */
    .switch-toggle { position: relative; display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
    .switch-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
    .switch-track { width: 46px; height: 26px; background-color: #cbd5e1; border-radius: 30px; transition: background-color 0.25s ease; position: relative; }
    .switch-thumb { position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; background: #fff; border-radius: 50%; transition: transform 0.25s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.25); }
    .switch-toggle input:checked + .switch-track { background-color: #10b981; }
    .switch-toggle input:checked + .switch-track .switch-thumb { transform: translateX(20px); }
    .switch-label-text { font-size: 14px; font-weight: 600; color: var(--text); }

    .form-footer { padding: 18px 28px; border-top: 1px solid var(--border); background: var(--surface2); display: flex; justify-content: flex-end; gap: 12px; }
    .btn-action { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; transition: all 0.18s ease; text-decoration: none; }
    .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 2px 8px rgba(79,70,229,0.28); }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-outline { background: var(--surface); border: 1px solid var(--border); color: var(--text); }
    .btn-outline:hover { background: var(--surface2); }
</style>
@endsection

@section('content')
<div class="form-header-bar">
    <div>
        <div class="header-breadcrumb">
            <a href="{{ route('categories.index') }}">Categories</a>
            <span>/</span>
            <span>{{ $isEdit ? 'Edit' : 'Add New' }}</span>
        </div>
        <div class="form-header-title">{{ $pageTitle }}</div>
    </div>
    <a href="{{ route('categories.index') }}" class="btn-action btn-outline">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Back to List
    </a>
</div>

<form method="POST" action="{{ $isEdit ? route('categories.update', $record->id) : route('categories.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="form-card">
        <div class="form-card-body">
            <div class="form-group">
                <label class="form-label">Category Name <span class="req">*</span></label>
                <input type="text" name="name" class="form-input" placeholder="e.g. Animals, Fruits, Numbers" value="{{ old('name', $isEdit ? $record->name : '') }}" required>
                @error('name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Category Image</label>
                <div class="image-preview-wrap">
                    <img id="imagePreview" class="image-preview {{ $isEdit && $record->image_url ? 'show' : '' }}" src="{{ $isEdit && $record->image_url ? $record->image_url : '' }}" alt="Preview">
                    <label class="image-drop" style="flex:1;">
                        <input type="file" name="image" accept="image/png,image/jpeg,image/webp,image/jpg" onchange="previewImage(this)">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="color: var(--primary); margin-bottom: 6px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <div style="font-size: 13.5px; font-weight: 600; color: var(--text);">Click to upload image</div>
                        <div class="form-hint">PNG, JPG or WEBP — up to 10MB</div>
                    </label>
                </div>
                @error('image') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Status</label>
                <label class="switch-toggle">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $isEdit ? $record->is_active : true) ? 'checked' : '' }}>
                    <span class="switch-track"><span class="switch-thumb"></span></span>
                    <span class="switch-label-text">On (Active in API)</span>
                </label>
                <div class="form-hint">When Off, this category and its videos are hidden from the category API.</div>
            </div>
        </div>
        <div class="form-footer">
            <a href="{{ route('categories.index') }}" class="btn-action btn-outline">Cancel</a>
            <button type="submit" class="btn-action btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                {{ $isEdit ? 'Update Category' : 'Save Category' }}
            </button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.classList.add('show'); };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
