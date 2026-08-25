@extends('layouts.admin')

@section('title', 'API List')
@section('page-title', 'API List')

@section('styles')
<style>
    /* ── Top Header Badge Bar ── */
    .api-header-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #8b5cf6;
        color: #ffffff;
        font-size: 13.5px;
        font-weight: 700;
        padding: 7px 16px;
        border-radius: 6px;
        margin-bottom: 22px;
        box-shadow: 0 2px 6px rgba(139,92,246,0.25);
    }
    .api-header-pill svg {
        width: 16px;
        height: 16px;
    }

    /* ── 2-Column Grid ── */
    .api-cards-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    @media (max-width: 992px) {
        .api-cards-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ── Single Endpoint Card ── */
    .api-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        padding: 22px 24px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .api-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.06);
    }

    .api-card-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 12px;
    }
    .api-card-title {
        font-size: 15px;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.2px;
    }
    .btn-copy-url {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #64748b;
        font-size: 11.5px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s;
    }
    .btn-copy-url:hover {
        background: #ede9fe;
        color: #6d28d9;
        border-color: #ddd6fe;
    }

    /* ── Method Row ── */
    .api-row {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .api-label {
        font-size: 12.5px;
        font-weight: 700;
        color: #334155;
    }
    .method-pill {
        display: inline-block;
        width: fit-content;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 800;
        font-family: monospace;
        letter-spacing: 0.5px;
    }
    .method-get {
        background: #00B0AA;
        color: #ffffff;
    }
    .method-post {
        background: #3b82f6;
        color: #ffffff;
    }

    /* ── URL Box ── */
    .api-url-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 12.5px;
        color: #e11d48;
        word-break: break-all;
        line-height: 1.45;
        font-weight: 500;
        user-select: all;
    }

    /* ── Parameters & Headers & Description ── */
    .api-meta-item {
        font-size: 12px;
        color: #475569;
        line-height: 1.5;
    }
    .api-meta-item .key {
        font-weight: 700;
        color: #6d28d9;
        font-family: monospace;
        font-size: 12px;
    }
    .api-meta-item .val {
        color: #e11d48;
        font-family: monospace;
        font-size: 12px;
    }
    .api-desc-text {
        font-size: 12.5px;
        color: #64748b;
        line-height: 1.5;
    }

    /* ── Toast Container ── */
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
<div class="toast-container" id="toastContainer"></div>

<!-- Top Purple Pill Badge -->
<div>
    <div class="api-header-pill">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="8" y1="6" x2="21" y2="6"/>
            <line x1="8" y1="12" x2="21" y2="12"/>
            <line x1="8" y1="18" x2="21" y2="18"/>
            <line x1="3" y1="6" x2="3.01" y2="6"/>
            <line x1="3" y1="12" x2="3.01" y2="12"/>
            <line x1="3" y1="18" x2="3.01" y2="18"/>
        </svg>
        <span>API List</span>
    </div>
</div>

<!-- 2-Column API Endpoints Grid -->
<div class="api-cards-grid">
    @foreach($endpoints as $idx => $api)
    <div class="api-card">
        <!-- Title & Copy Button -->
        <div class="api-card-title-row">
            <h3 class="api-card-title">{{ $idx + 1 }}. {{ $api['name'] }}</h3>
            <button type="button" class="btn-copy-url" onclick="copyApiUrl('{{ $api['full_url'] }}')">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                Copy URL
            </button>
        </div>

        <!-- Method -->
        <div class="api-row">
            <span class="api-label">Method:</span>
            <span class="method-pill method-{{ strtolower($api['method']) }}">{{ $api['method'] }}</span>
        </div>

        <!-- URL -->
        <div class="api-row">
            <span class="api-label">URL:</span>
            <div class="api-url-box">{{ $api['full_url'] }}</div>
        </div>

        <!-- Parameters (if present) -->
        @if(!empty($api['params']))
        <div class="api-row">
            <span class="api-label">Parameters:</span>
            @foreach($api['params'] as $param)
                <div class="api-meta-item">
                    <span class="key">{{ $param['name'] }}</span> : 
                    <span style="color: {{ $param['required'] ? '#ef4444' : '#64748b' }}; font-size:11px; font-weight:600;">({{ $param['required'] ? 'required' : 'optional' }})</span> 
                    <span>{{ $param['description'] }}</span>
                </div>
            @endforeach
        </div>
        @endif

        <!-- Headers -->
        <div class="api-row">
            <span class="api-label">Headers:</span>
            @if(isset($api['headers']) && is_array($api['headers']))
                @foreach($api['headers'] as $hKey => $hVal)
                    <div class="api-meta-item">
                        <span class="key">{{ $hKey }}</span> : <span class="val">{{ $hVal }}</span>
                    </div>
                @endforeach
            @else
                <div class="api-meta-item">
                    <span class="key">Authorization</span> : <span class="val">Bearer &lt;YOUR_API_TOKEN&gt;</span>
                </div>
                <div class="api-meta-item">
                    <span class="key">Accept</span> : <span class="val">application/json</span>
                </div>
            @endif
        </div>

        <!-- Description -->
        <div class="api-row">
            <span class="api-label">Description:</span>
            <p class="api-desc-text">{{ $api['description'] }}</p>
        </div>
    </div>
    @endforeach
</div>

@endsection

@section('scripts')
<script>
// Auto remove toast after 5 seconds (5000ms)
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

function copyApiUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        showToast('API URL copied to clipboard: ' + url, 'success');
    });
}
</script>
@endsection
