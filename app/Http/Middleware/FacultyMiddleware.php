<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FacultyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->user_type === 'faculty') {
            return $next($request);
        }
        return response()->json(['message' => 'Access denied. Faculty only.'], 403);
    }
}
