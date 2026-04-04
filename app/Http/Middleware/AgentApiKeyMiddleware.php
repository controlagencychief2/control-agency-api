<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgentApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = config('app.agent_api_key');
        
        if (!$apiKey) {
            return response()->json([
                'error' => 'API authentication not configured'
            ], 500);
        }

        $providedKey = $request->bearerToken();

        if (!$providedKey || $providedKey !== $apiKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing API key'
            ], 401);
        }

        return $next($request);
    }
}
