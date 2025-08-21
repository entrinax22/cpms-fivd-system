<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustChangePassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $mustChange = $user->must_change_password;
            $expired = $user->password_expires_at && now()->greaterThan($user->password_expires_at);

            // Allow access to the change-password pages and logout even if flagged
            $allowedRoutes = ['password.change.form','password.change.update','logout'];
            if (($mustChange || $expired) && !in_array(optional($request->route())->getName(), $allowedRoutes)) {
                return redirect()->route('password.change.form');
            }
        }

        return $next($request);
    }
}
