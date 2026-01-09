<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PermissionStore
{
    private static $apiUrl = 'https://gateway.pdamkotasmg.co.id/api-gw/portal-pegawai/api/auth/permission-names';

    /**
     * Get bearer token from Authorization header
     */
    private static function getBearerToken()
    {
        return request()->header('Authorization');
    }

    /**
     * Fetch permissions from API
     */
    private static function fetchFromApi()
    {
        try {
            $token = self::getBearerToken();

            if (!$token) {
                Log::warning('PermissionStore: No authorization token available');
                return [];
            }

            $response = Http::withHeaders([
                'Authorization' => $token, // Token sudah include "Bearer " prefix
                'Accept' => 'application/json',
            ])->timeout(10)->get(self::$apiUrl);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']['permissions']) && is_array($data['data']['permissions'])) {
                    return $data['data']['permissions'];
                }
            }

            Log::error('PermissionStore: API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];

        } catch (\Exception $e) {
            Log::error('PermissionStore: Exception when fetching permissions', [
                'message' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Load permissions from API
     */
    public static function load()
    {
        $token = self::getBearerToken();

        if (!$token) {
            return [];
        }

        return self::fetchFromApi();
    }

    /**
     * Legacy method for backward compatibility
     * Now returns all permissions for current user
     */
    public static function getPermissionsFor($npp = null)
    {
        return self::load();
    }

    /**
     * Check if user has specific permission
     */
    public static function hasPermission($permission)
    {
        $permissions = self::load();
        return in_array($permission, $permissions);
    }

    /**
     * Check if user has any of the given permissions
     */
    public static function hasAnyPermission(array $permissions)
    {
        $userPermissions = self::load();
        return !empty(array_intersect($permissions, $userPermissions));
    }

    /**
     * Check if user has all of the given permissions
     */
    public static function hasAllPermissions(array $permissions)
    {
        $userPermissions = self::load();
        return empty(array_diff($permissions, $userPermissions));
    }
}
