<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roles): Response
    {
        $user = User::find(auth()->id());
        $userRole = $user->getRole->role;

        $roleList = explode("|", $roles);
        foreach ($roleList as $role) {
            if ($userRole == $role) {
                return $next($request);
            }
        }
        return response()->json(['message' => 'permission denied']);
    }
}
