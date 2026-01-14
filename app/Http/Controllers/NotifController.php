<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\InternalNotification;

class NotifController extends Controller
{
    public function getNotifications(Request $request, $npp)
    {
        try {
            $externalUser = $request->attributes->get('external_user');
            $token = $request->bearerToken();
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

            $externalUser = $response->json('data.user');

            if (!$externalUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data user eksternal tidak ditemukan.'
                ], 401);
            }


            if (($externalUser['npp'] ?? null) !== $npp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak.'
                ], 403);
            }

            if ($request->has('stream') && (int)$request->stream === 1) {
                return $this->streamNotifications($npp, $externalUser);
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
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function streamNotifications($npp)
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        while (true) {

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

            ob_flush();
            flush();

            sleep(5);
        }
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
            ->update(['status' => 'read']);

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
