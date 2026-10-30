<?php

namespace App\Http\Middleware;

use App\Support\SecureMediaHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * On admin HTML responses, rewrites every public image URL
 *   .../uploads/<path>.<img-ext>
 * into an opaque, expiring token URL  /secure-media?token=...
 * so the real file path never reaches the browser, DOM, or view-source.
 *
 * Images only — videos are served through an id-based POST stream, so no
 * video URL is ever emitted into the page to rewrite.
 */
class ProtectAdminMediaUrls
{
    /** Image extensions that get rewritten. */
    private const IMAGE_EXTENSIONS = 'png|jpe?g|webp|gif|svg|bmp|ico';

    /** Token lifetime for images (they load immediately on render). */
    private const IMAGE_TTL_SECONDS = 30;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $html = $response->getContent();
        if (!is_string($html) || $html === '' || !str_contains($html, 'uploads/')) {
            return $response;
        }

        // Match an (optional scheme+host) + /uploads/<path>.<ext>, stopping at the
        // surrounding quote or angle bracket. Space-aware so "Old Dance" folders work.
        $pattern = '#(?:https?://[^/"\'<>]+)?/?uploads/[^"\'<>\\\\]+?\.(?:' . self::IMAGE_EXTENSIONS . ')#i';

        $html = preg_replace_callback($pattern, function ($m) {
            $url = $m[0];

            // Reduce to the public-relative path starting at "uploads/".
            $pos = stripos($url, 'uploads/');
            if ($pos === false) {
                return $url;
            }
            $relative = substr($url, $pos);

            // Decode any HTML/URL-escaped spaces so the path matches disk.
            $relative = str_replace(['&amp;', '%20'], ['&', ' '], $relative);

            $token = SecureMediaHelper::encode($relative, self::IMAGE_TTL_SECONDS);

            return url('/secure-media') . '?token=' . $token;
        }, $html);

        // setContent() overwrites Response::$original (the View). Preserve it so
        // viewData()/tests and anything reading the original view still work.
        $original = property_exists($response, 'original') ? $response->original : null;
        $response->setContent($html);
        if ($original !== null && property_exists($response, 'original')) {
            $response->original = $original;
        }

        return $response;
    }
}
