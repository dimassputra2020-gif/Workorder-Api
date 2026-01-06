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
        'jenis_pekerjaan_id',
        'kode_barang',
        'kd_satker',
        'uraian_pekerjaan',
        'stafs',
        'penanggung_jawab_npp',
        'penanggung_jawab_tlp',
        'penanggung_jawab_name',
        'penanggung_jawab_ttd',
        'menyetujui',
        'menyetujui_name',
        'menyetujui_npp',
        'menyetujui_tlp',
        'menyetujui_ttd',
        'mengetahui',
        'mengetahui_name',
        'mengetahui_npp',
        'mengetahui_tlp',
        'mengetahui_ttd',
        'npp_kepala_satker',
        'file',
        'status_id',
        'no_surat',
        'no_referensi',
        'kode_barang'
    ];

    protected $casts = [
        'stafs' => 'array',
        'file' => 'array',
        'timeline' => 'array',
    ];


    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class, 'uuid_pengajuan', 'uuid_pengajuan')
            ->orderBy('created_at', 'asc');
    }

   public function status()
{
    return $this->belongsTo(MasterStatus::class, 'status_id');
}

public function jenisPekerjaan()
{
    return $this->belongsTo(
        \App\Models\MasterJenisPekerjaan::class,
        'jenis_pekerjaan_id',
        'id'
    );
}

}
