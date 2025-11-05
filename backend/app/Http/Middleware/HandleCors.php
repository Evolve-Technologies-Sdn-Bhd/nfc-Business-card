<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Handle preflight requests
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        }

        // Allow all origins in development
        // DEPLOYMENT NOTE: Update FRONTEND_URL in .env for each environment
        // ALPHA: 'https://alpha.yourdomain.com'
        // BETA: 'https://beta.yourdomain.com'
        // PRODUCTION: 'https://yourdomain.com'
        $origin = env('FRONTEND_URL', 'http://localhost:3000');
        if (app()->environment('local')) {
            $origin = $request->header('Origin') ?: $origin;
        }

        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'false');
        $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours

        return $response;
    }
}
