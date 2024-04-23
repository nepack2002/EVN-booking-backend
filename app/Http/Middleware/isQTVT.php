<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isQTVT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         if ($request->user() && ($request->user()->isQTVT() || $request->user()->isAdmin())) {
             return $next($request);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }
}