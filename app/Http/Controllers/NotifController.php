<?php

namespace App\Http\Controllers;

use App\Models\InternalNotification;
use Illuminate\Http\Request;

class NotifController extends Controller
{
  public function getNotifications(Request $request, $npp)
{
    try {
        $token = $request->bearerToken() ?: $request->query('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan'
            ], 401);
        }

        $baseUrl = 'https://gateway.pdamkotasmg.co.id/api-gw-dev/portal-pegawai';
        
        $response = \Illuminate\Support\Facades\Http::withToken($token)
            ->get($baseUrl . '/api/auth/me');

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah kedaluwarsa'
            ], 401);
        }

        $externalData = $response->json('data.user');
        $externalNpp = $externalData['npp'] ?? null;

        if (!$externalNpp || $externalNpp !== $npp) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. NPP tidak sesuai.'
            ], 403);
        }

        if ($request->query('stream') == '1') {
            return $this->streamNotifications($npp);
        }

        $notifications = InternalNotification::where('npp', $npp)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $unreadCount = InternalNotification::where('npp', $npp)
            ->where('status', 'unread')
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'data' => $notifications,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Server Error: ' . $e->getMessage()
        ], 500);
    }
}
private function streamNotifications($npp)
{
    return response()->stream(function () use ($npp) {
        while (true) {
            if (connection_aborted()) {
                break;
            }

            $notif = InternalNotification::where('npp', $npp)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            $unreadCount = InternalNotification::where('npp', $npp)
                ->where('status', 'unread')
                ->count();

            echo "data: " . json_encode([
                'success' => true,
                'unread_count' => $unreadCount,
                'data' => $notif
            ]) . "\n\n";

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();

            sleep(5);
        }
    }, 200, [
        'Content-Type'      => 'text/event-stream',
        'Cache-Control'     => 'no-cache',
        'Connection'        => 'keep-alive',
        'X-Accel-Buffering' => 'no',
    ]);
}



    public function getAllNotifications($npp)
    {
        $notifications = InternalNotification::where('npp', $npp)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }


    public function update($id)
    {
        $updated = InternalNotification::where('id', $id)
            ->where('status', 'unread')
            ->update(['status' =>'read']);

        return response()->json([
            'success' => true,
            'message' => $updated > 0 
                ? 'notifikasi unread telah ditandai sebagai read.'
                : 'Tidak ada notifikasi unread.'
        ]);
    }

    public function markAllAsRead($npp)
{
    $updated = InternalNotification::where('npp', $npp)
        ->where('status', 'unread')
        ->update([
            'status' => 'read',
            'updated_at' => now()
        ]);

    return response()->json([
        'success' => true,
        'message' => $updated > 0
            ? 'Semua notifikasi berhasil ditandai sebagai dibaca.'
            : 'Tidak ada notifikasi unread.',
        'updated_count' => $updated
    ]);
}
}
