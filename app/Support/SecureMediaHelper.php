<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

/**
 * Turns a public-relative media path (e.g. "uploads/.../thumb.webp") into a
 * URL-safe, encrypted, expiring token and back. The token leaks nothing about
 * the real path and dies after its TTL.
 */
class SecureMediaHelper
{
    /** Only files under this public folder may ever be tokenized/streamed. */
    public const BASE_FOLDER = 'uploads';

    /**
     * Encode a relative path into an opaque token valid for $ttl seconds.
     */
    public static function encode(string $relativePath, int $ttl = 30): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        $payload = Crypt::encryptString(json_encode([
            'p' => $relativePath,
            'e' => time() + max(1, $ttl),
        ]));

        // Make the base64 ciphertext URL-safe (so '+' '/' '=' survive query strings).
        return rtrim(strtr($payload, '+/', '-_'), '=');
    }

    /**
     * Decode a token back to its relative path, or null if it is invalid,
     * expired, tampered, escapes the base folder, or points outside uploads/.
     */
    public static function decode(?string $token): ?string
    {
        if (!$token) {
            return null;
        }

        $b64 = strtr($token, '-_', '+/');
        $b64 .= str_repeat('=', (4 - strlen($b64) % 4) % 4);

        try {
            $data = json_decode(Crypt::decryptString($b64), true);
        } catch (\Throwable $e) {
            return null;
        }

        if (!is_array($data) || empty($data['p']) || empty($data['e'])) {
            return null;
        }

        if (time() > (int) $data['e']) {
            return null; // expired
        }

        $path = ltrim(str_replace('\\', '/', (string) $data['p']), '/');

        // Reject path traversal and anything outside the base folder.
        if (str_contains($path, '..') || !str_starts_with($path, self::BASE_FOLDER . '/')) {
            return null;
        }

        return $path;
    }
}
