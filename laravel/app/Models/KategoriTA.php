<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriTA extends Model
{
    use HasFactory;

    protected $table = 'kategori_ta';

    protected $fillable = [
        'kode',
        'nama',
    ];
}
