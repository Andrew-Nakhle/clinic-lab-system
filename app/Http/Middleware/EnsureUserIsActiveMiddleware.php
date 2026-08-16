<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActiveMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if($user->status===UserStatus::Inactive){
            return response()->json([
                'meesage'=>'Your account is inactive. You cannot perform this action.'
            ],403);
        }


        if ($user->status === UserStatus::Banned) {
            return response()->json([
                'message' => 'Your account has been banned.'
            ], 403);
        }



        return $next($request);
    }
}
