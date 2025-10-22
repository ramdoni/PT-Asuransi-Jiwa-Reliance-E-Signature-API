<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $origin = $request->header('Origin');

        $allowedOrigins = [
            'http://localhost:3000',
            'http://relisign.entigi.co.id',
            'https://relisign.entigi.co.id',
        ];

        if (in_array($origin, $allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $origin);
        }

        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->header('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}