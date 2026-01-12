<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $allowedOrigins = [
            'http://relisign.entigi.co.id',
            'https://relisign.entigi.co.id',
            'http://localhost:3000',
            'https://ajrisign.id',
        ];

        $origin = $request->header('Origin');

        // 🔥 HANDLE PREFLIGHT FIRST
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        $response->headers->set(
            'Access-Control-Allow-Methods',
            'GET, POST, PUT, PATCH, DELETE, OPTIONS'
        );

        $response->headers->set(
            'Access-Control-Allow-Headers',
            'Content-Type, Authorization, X-Requested-With'
        );

        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}