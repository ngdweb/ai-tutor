<?php

use App\Support\SecureMediaHelper;

if (!function_exists('secure_media')) {
    /**
     * Turn a public-relative media path (or a full /uploads URL) into an
     * opaque, expiring token URL. External http(s) URLs are returned unchanged.
     */
    function secure_media(?string $pathOrUrl, int $ttl = 30): ?string
    {
        if (!$pathOrUrl) {
            return null;
        }

        // Full external URL (e.g. CDN) — leave as-is.
        if (preg_match('#^https?://#i', $pathOrUrl)) {
            $pos = stripos($pathOrUrl, '/uploads/');
            if ($pos === false) {
                return $pathOrUrl;
            }
            $pathOrUrl = ltrim(substr($pathOrUrl, $pos), '/');
        }

        $relative = ltrim(str_replace('\\', '/', $pathOrUrl), '/');

        if (!str_starts_with($relative, SecureMediaHelper::BASE_FOLDER . '/')) {
            return $pathOrUrl;
        }

        return url('/secure-media') . '?token=' . SecureMediaHelper::encode($relative, $ttl);
    }
}
