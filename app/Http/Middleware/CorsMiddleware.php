<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $origin = $request->headers->get('Origin');

        $allowedOrigins = [
            env('FRONTEND_URL', 'http://localhost:3000'),
            'http://relisign.entigi.co.id',
            'https://relisign.entigi.co.id',
        ];

        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        // Jika request OPTIONS (preflight), langsung return response kosong
        if ($request->getMethod() === "OPTIONS") {
            $response->setStatusCode(200);
            $response->setContent('');
        }

        return $response;
    }
}