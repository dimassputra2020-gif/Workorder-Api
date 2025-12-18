<?php

namespace App\Http\Controllers;

use App\Models\InternalNotification;

class NotifController extends Controller
{
    public function getNotifications($npp)
    {
        
        if (request()->has('stream') && request()->stream == 1) {
            return $this->streamNotifications($npp);
        }

        $notifications = InternalNotification::where('npp', $npp)
            ->orderBy('created_at', 'desc')
            ->limit(20)
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

            echo "data: " . json_encode([
                'success' => true,
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
