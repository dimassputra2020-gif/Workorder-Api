<?php

namespace App\Http\Controllers;

use App\Models\InternalNotification;

class NotifController extends Controller
{
    public function getNotifications($npp)
    {
         $notifications = InternalNotification::where('npp', $npp)
            ->where('status', 'unread')
            ->get();

        if ($notifications->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada notifikasi baru.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function update($npp)
    {
        $updated = InternalNotification::where('npp', $npp)
            ->where('status', 'unread')
            ->update(['status' => 'read']);

        return response()->json([
            'success' => true,
            'message' => $updated > 0 
                ? 'Semua notifikasi unread telah ditandai sebagai read.'
                : 'Tidak ada notifikasi unread.'
        ]);
    }

    public function deleteNotification($id)
{
    $notif = InternalNotification::find($id);

    if (!$notif) {
        return response()->json([
            'success' => false,
            'message' => 'Notifikasi tidak ditemukan'
        ], 404);
    }

    $notif->delete();

    return response()->json([
        'success' => true,
        'message' => 'Notifikasi berhasil dihapus'
    ]);
}

}
