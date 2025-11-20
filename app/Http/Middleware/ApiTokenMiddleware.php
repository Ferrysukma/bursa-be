<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\ResponseWebTrait;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = str_replace('Bearer ', '', $request->header('Authorization'));
        if (!$token) {
            return ResponseWebTrait::error(false, 'Unauthorized', 403);
        }

        $apiToken   = DB::table('api_tokens')
        ->where('token', hash('sha256', $token))
        ->where('expires_at', '>=', now())
        ->first();
        if (!$apiToken) {
            return ResponseWebTrait::error(false, 'Token Expired', 403);
        }

        $request->merge(['token_user_id' => $apiToken->user_id]);

        return $next($request);
    }
}
