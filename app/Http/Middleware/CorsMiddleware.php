<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $origin = $request->header('Origin');

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://wa-center.entigi.co.id/v1/wa/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('phone' => '08881264670','message' => "is origin : ".$origin),
        CURLOPT_HTTPHEADER => array(
            'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC93YS5lbnRpZ2kuY28uaWRcL3YxXC9sb2dpbiIsImlhdCI6MTcwMjYyNjY2NSwiZXhwIjoxNzAyNzEzMDY1LCJuYmYiOjE3MDI2MjY2NjUsImp0aSI6Im1Db2ozbEZ4SU5ZSHJheFgiLCJzdWIiOiIwNDExNTUxZS03N2IwLTExZWUtYmEyNy1mMTdjNzcyZmUzZjciLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.cTRqL3GJWJk6j6JEKoeRAzr80EZMlo0xJCuYCIVUQ6Q'
        ),
        ));

        $response = curl_exec($curl);
        
        curl_close($curl);

        $allowedOrigins = ["*"];
        
        // Cek apakah origin diizinkan
        $allowOrigin = in_array($origin, $allowedOrigins) ? $origin : $allowedOrigins[0];

        // Jika request adalah OPTIONS (preflight), langsung kirim respons kosong 204
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204)
                ->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        // Untuk request biasa
        $response = $next($request);

        return $response
            ->header('Access-Control-Allow-Origin', $allowOrigin)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}