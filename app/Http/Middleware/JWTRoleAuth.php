<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JWTRoleAuth extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role = null)
    {
        try {
            $token_role = $this->auth->parseToken()->getClaim('role');
        } catch (JWTException $e) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        if ($token_role != $role) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        return $next($request);
    }
}
