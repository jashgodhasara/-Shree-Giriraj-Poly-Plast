<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrettyPrintJson
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Re-encode JSON with pretty printing for API routes
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $response->setEncodingOptions(
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return $response;
    }
}
