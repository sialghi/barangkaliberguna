<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PendaftaranSempro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pendaftaran_sempro';

    protected $fillable = [
        'id_mahasiswa',
        'id_periode_sempro',
        'judul_proposal',
        'id_calon_dospem_1',
        'id_calon_dospem_2',
        'file_proposal_skripsi',
        'file_transkrip_nilai',
        'id_kategori_ta',
        'status',
        'alasan',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'id_mahasiswa')->withTrashed();
    }

    public function calonDospem1()
    {
        return $this->belongsTo(User::class, 'id_calon_dospem_1');
    }

    public function calonDospem2()
    {
        return $this->belongsTo(User::class, 'id_calon_dospem_2');
    }

    public function periodeSempro()
    {
        return $this->belongsTo(PeriodeSempro::class, 'id_periode_sempro');
    }

    public function kategoriTa()
    {
        return $this->belongsTo(KategoriTA::class, 'id_kategori_ta');
    }
}
