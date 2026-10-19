<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class VerifySignedGetApi
{
    public function handle(Request $request, Closure $next): Response
    {
        // This middleware is specifically for this one GET endpoint.
        if (!$request->isMethod('GET') || $request->path() !== 'api/get-api-key') {
            return response()->json([
                'success' => false,
                'message' => 'Not found',
            ], 404);
        }

        $apiKey = (string) $request->header('X-API-KEY', '');
        $timestamp = (string) $request->header('X-TIMESTAMP', '');
        $nonce = (string) $request->header('X-NONCE', '');
        $signature = (string) $request->header('X-SIGNATURE', '');

        $expectedKey = (string) config('services.private_api.key');
        $secret = (string) config('services.private_api.secret');

        if ($expectedKey === '' || $secret === '') {
            return response()->json([
                'success' => false,
                'message' => 'Server API credentials are not configured',
            ], 500);
        }

        if (
            $apiKey === '' ||
            $timestamp === '' ||
            $nonce === '' ||
            $signature === '' ||
            !hash_equals($expectedKey, $apiKey)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Allow only a small clock difference.
        if (!ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 60) {
            return response()->json([
                'success' => false,
                'message' => 'Request expired',
            ], 401);
        }

        // Require 16 random bytes encoded as 32 hexadecimal characters.
        if (!preg_match('/^[a-f0-9]{32}$/i', $nonce)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid nonce',
            ], 401);
        }

        if (!preg_match('/^[a-f0-9]{64}$/i', $signature)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 401);
        }

        // Keep this canonical string identical in the cURL client.
        $method = 'GET';
        $path = '/api/get-api-key';
        $bodyHash = hash('sha256', '');

        $payload = $method . "\n"
            . $path . "\n"
            . $timestamp . "\n"
            . $nonce . "\n"
            . $bodyHash;

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expectedSignature, strtolower($signature))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 401);
        }

        // Cache::add is atomic with supported cache stores. Use Redis or
        // another shared atomic cache store if requests reach multiple servers.
        $nonceCacheKey = 'private-api:nonce:' . hash('sha256', $apiKey . ':' . $nonce);

        if (!Cache::add($nonceCacheKey, true, now()->addSeconds(120))) {
            return response()->json([
                'success' => false,
                'message' => 'Request already used',
            ], 401);
        }

        return $next($request);
    }
}
