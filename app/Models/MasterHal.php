<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterHal extends Model
{
    protected $table = 'masterhal'; // <-- PENTING
    protected $fillable = ['kode', 'nama_jenis','status'];
}