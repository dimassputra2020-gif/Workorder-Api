<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class PermissionStore
{
    private static $path;

    private static function getPath()
    {
        if (!self::$path) {
            self::$path = __DIR__ . '/../../storage/app/permissions.json';
        }
        return self::$path;
    }

    public static function load()
    {
        $path = self::getPath();
        
        if (!file_exists($path)) {
            file_put_contents($path, json_encode([]));
            return [];
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);
        
        return is_array($data) ? $data : [];
    }

    public static function save($data)
    {
        $path = self::getPath();
        $json_data = json_encode($data, JSON_PRETTY_PRINT);
        
        file_put_contents($path, $json_data); 
    }

    public static function setPermissions($npp, $permissions)
    {
        $data = self::load();
        $data[$npp] = $permissions;
        self::save($data);
    }

    public static function getPermissionsFor($npp)
    {
        $data = self::load();
        return $data[$npp] ?? [];
    }
}

