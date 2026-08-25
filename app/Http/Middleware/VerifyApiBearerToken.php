<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiBearerToken
{
    /**
     * Handle an incoming API request.
     * Validates that the client provided the expected Bearer token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = env('API_TOKEN', 'IWGkI4GkjRt6fScJL1oBCCe7MYINeUGAjRiDnVMZmujUOtUbVx');

        // Extract Bearer token from header
        $providedToken = $request->bearerToken();

        // Fallback checks for Authorization header without 'Bearer ' prefix or custom headers
        if (!$providedToken) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                $providedToken = trim(substr($authHeader, 7));
            } elseif ($authHeader) {
                $providedToken = trim($authHeader);
            } else {
                $providedToken = $request->header('API_TOKEN') ?? $request->header('api_token') ?? $request->input('api_token');
            }
        }

        if (empty($providedToken) || !hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Invalid or missing API Bearer token.',
                'data'    => null,
            ], 401);
        }

        return $next($request);
    }
}
