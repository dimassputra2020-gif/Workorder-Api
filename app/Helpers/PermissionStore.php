<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class PermissionStore
{
    private static $file = 'permissions.json';

    // Get JSON file content
    public static function load()
    {
        if (!Storage::exists(self::$file)) {
            Storage::put(self::$file, json_encode([], JSON_PRETTY_PRINT));
        }

        return json_decode(Storage::get(self::$file), true);
    }

    // Save JSON file content
    public static function save($data)
    {
        Storage::put(self::$file, json_encode($data, JSON_PRETTY_PRINT));
    }

    // Set permissions for NPP
    public static function setPermissions($npp, $permissions)
    {
        $data = self::load();
        $data[$npp] = $permissions;
        self::save($data);
    }

    // Get permissions for NPP
    public static function getPermissionsFor($npp)
    {
        $data = self::load();
        return $data[$npp] ?? [];
    }
}
