<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionStore;

class PermissionController extends Controller
{
    
    public function setPermissions(Request $request)
    {
        $externalUser = $request->attributes->get('external_user');

        if (!$externalUser) {
            return response()->json([
                "success" => false,
                "message" => "User eksternal tidak valid"
            ], 401);
        }

        $request->validate([
            'npp' => 'required|string',
            'permissions' => 'required|array'
        ]);

        PermissionStore::setPermissions(
            $request->npp,
            $request->permissions
        );

        return response()->json([
            "success" => true,
            "message" => "Permissions updated",
            "npp" => $request->npp,
            "permissions" => $request->permissions
        ]);
    }

    
    public function getPermissions(Request $request, $npp)
    {
        $externalUser = $request->attributes->get('external_user');

        if (!$externalUser) {
            return response()->json([
                "success" => false,
                "message" => "User eksternal tidak valid"
            ], 401);
        }

        $permissions = PermissionStore::getPermissionsFor($npp);

        return response()->json([
            "success" => true,
            "npp" => $npp,
            "permissions" => $permissions
        ]);
    }
}
