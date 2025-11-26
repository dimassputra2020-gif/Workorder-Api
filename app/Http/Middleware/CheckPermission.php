<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $externalUser = $request->attributes->get('external_user');

        if (!$externalUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: user tidak ditemukan'
            ], 401);
        }

        // Pastikan eksternal_user berisi array permissions
        $userPermissions = $externalUser['permissions'] ?? [];

        if (!in_array($permission, $userPermissions)) {
            return response()->json([
                'success' => false,
                'message' => "Forbidden: anda tidak punya akses ke permission $permission"
            ], 403);
        }

        return $next($request);
    }
}
