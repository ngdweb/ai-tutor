<?php

namespace App\Http\Controllers;

use App\Support\SecureMediaHelper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class SecureMediaController extends Controller
{
    private const MIME_MAP = [
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/x-icon',
    ];

    /**
     * Stream the file referenced by an encrypted, expiring token.
     * Rejects expired / tampered / traversal / outside-uploads / missing files.
     */
    public function stream(Request $request): Response|BinaryFileResponse
    {
        $path = SecureMediaHelper::decode($request->query('token'));

        if ($path === null) {
            return response('Forbidden', 403);
        }

        $full = public_path($path);
        $real = realpath($full);
        $base = realpath(public_path(SecureMediaHelper::BASE_FOLDER));

        // Final guard: resolved file must physically sit inside public/uploads.
        if ($real === false || $base === false || !str_starts_with($real, $base)) {
            return response('Forbidden', 403);
        }

        if (!is_file($real)) {
            return response('Not found', 404);
        }

        $ext  = strtolower(pathinfo($real, PATHINFO_EXTENSION));
        $mime = self::MIME_MAP[$ext] ?? 'application/octet-stream';

        return response()->file($real, [
            'Content-Type'        => $mime,
            'Cache-Control'       => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'inline',
        ]);
    }
}
