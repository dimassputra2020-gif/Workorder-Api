<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spk extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid_pengajuan',
        'tanggal',
        'jenis_pekerjaan',
        'kode_barang',
        'uraian_pekerjaan',
        'stafs',
        'penanggung_jawab_npp',
        'penanggung_jawab_tlp',
        'penanggung_jawab_name',
        'mengetahui_npp',
        'mengetahui_name',
        'mengetahui',
        'file',
        'status',
        'ttd_mengetahui',
        'no_surat',
        'no_referensi'
    ];

    protected $casts = [
        'stafs' => 'array',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}
