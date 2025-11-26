<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterStatus extends Model
{
    // Nama tabel di database
    protected $table = 'master_status';

    // Kolom yang bisa diisi
    protected $fillable = ['code', 'name'];
}
