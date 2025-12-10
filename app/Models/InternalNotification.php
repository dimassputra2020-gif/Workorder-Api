<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalNotification extends Model
{
    protected $table = 'internal_notifications';

    protected $fillable = [
        'npp',
        'judul',
        'pesan',
        'status',
        'uuid_pengajuan'
    ];
}
