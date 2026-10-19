<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PrivateApiController extends Controller
{
    public function getApiKey(): JsonResponse
    {
        // Return only the data the authorized caller needs: the key stored in
        // OPENAI_API_KEY (.env). Never return API_SECRET.
        $key = (string) config('services.private_api.openai_key');

        if ($key === '') {
            return response()->json([
                'success' => false,
                'message' => 'API key is not configured on the server',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'API request verified',
            'data' => [
                'status' => 'authorized',
                'api_key' => $key,
            ],
        ]);
    }
}
