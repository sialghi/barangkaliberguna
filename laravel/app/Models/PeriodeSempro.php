<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodeSempro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'periode_sempro';

    protected $fillable = [
        'id_program_studi',
        'id_fakultas',
        'periode',
        'tanggal',
    ];

    public function pendaftaranSempro()
    {
        return $this->hasOne(PendaftaranSempro::class, 'id_periode_sempro');
    }
}
