<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and has admin privileges
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        // If not an admin, redirect to home or show an unauthorized message
        return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
    }
}
