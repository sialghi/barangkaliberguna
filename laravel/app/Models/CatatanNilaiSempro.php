<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatatanNilaiSempro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'catatan_nilai_sempro';

    protected $fillable = [
        'id_nilai_sempro',
        'id_penguji',
        'catatan_judul',
        'catatan_latar_belakang',
        'catatan_identifikasi_masalah',
        'catatan_pembatasan_masalah',
        'catatan_perumusan_masalah',
        'catatan_penelitian_terdahulu',
        'catatan_metodologi_penelitian',
        'catatan_referensi'
    ];

    public function nilaiSempro()
    {
        return $this->belongsTo(NilaiSempro::class, 'id_nilai_sempro');
    }

    public function penguji()
    {
        return $this->belongsTo(User::class, 'id_penguji');
    }
}
