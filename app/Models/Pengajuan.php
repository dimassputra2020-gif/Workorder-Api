<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'hal',
        'kepada',
        'satker',
        'kode_barang',
        'keterangan',
        'file',
        'status',
        'uuid',
        'is_deleted',
        'name_pelapor',
        'npp_pelapor',
        'tlp_pelapor',
        'mengetahui',
        'no_surat',
        'mengetahui_name',
        'mengetahui_npp',
        'mengetahui_tlp',
        'ttd_pelapor',
        'ttd_mengetahui',
        'no_referensi'
    ];

    
    protected $casts = [
        'file' => 'array', 
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'external_user_token', 'api_token');
    }
    public function masterhal()
    {
        return $this->belongsTo(MasterHal::class, 'hal_id','id');
    }

}