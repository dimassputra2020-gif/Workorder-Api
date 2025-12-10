<?php

namespace App\Helpers;

use App\Models\InternalNotification;

class Notif
{
    public static function push( $uuid_pengajuan,$npp, $judul, $pesan)
    {
        return InternalNotification::create([
            'uuid_pengajuan' => $uuid_pengajuan,
            'npp' => $npp,
            'judul' => $judul,
            'pesan' => $pesan,
            'status' => 'unread',
        ]);
    }
}
