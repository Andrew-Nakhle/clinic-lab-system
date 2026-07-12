<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->status !== UserStatus::Active) {
            return response()->json([
                'status'  => false,
                'message' => 'Your account is deactivated. Please contact the super admin.'
            ], 403);
        }
        return $next($request);
    }
}
