<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('user.login');
        }

        $user = auth()->user();
        
        // Check if user's role is in the allowed roles
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // If API request, return 403 Forbidden
        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('You do not have permission to access this resource.'),
                'status' => 'forbidden'
            ], 403);
        }

        // If web request, redirect to dashboard
        return redirect()->route('user.dashboard')
            ->with('error', __('You do not have permission to access this page.'));
    }
}
