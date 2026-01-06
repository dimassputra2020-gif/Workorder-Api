<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'hal_id',
        'kd_satker',
        'npp_kepala_satker',
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
        'no_referensi',
        'catatan_status'
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


    public function timelines()
{
    return $this->hasMany(Timeline::class, 'uuid_pengajuan', 'uuid')
                ->orderBy('created_at', 'asc');
}

}