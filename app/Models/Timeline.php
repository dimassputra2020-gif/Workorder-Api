<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    protected $table = 'timelines';

    protected $fillable = [
        'uuid_pengajuan',
        'source',
        'title',
        'status',
        'message',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'uuid_pengajuan', 'uuid');
    }
}