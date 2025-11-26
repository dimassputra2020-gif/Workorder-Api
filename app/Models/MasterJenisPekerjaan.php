<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJenisPekerjaan extends Model
{
    protected $table = 'masterjenispekerjaan';

    protected $fillable = [
        'kode',
        'nama_pekerjaan',
        'status'
    ];

    public $timestamps = false;
}
 