<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetUserId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            $request->attributes->set('userId', $user->id);
            Log::info('userid:'.$request->attributes->get('userId'));
        } else {
            $request->attributes->set('userId', null);
        }
        return $next($request);
    }
}
