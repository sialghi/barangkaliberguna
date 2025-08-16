<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PendaftaranSkripsi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pendaftaran_skripsi';

    protected $fillable = [
        'id_mahasiswa',
        'judul_skripsi',
        'waktu_ujian',
        'id_dosen_pembimbing_akademik',
        'id_dosen_pembimbing_1',
        'id_dosen_pembimbing_2',
        'id_calon_penguji_1',
        'id_calon_penguji_2',
        'calon_penguji_3_name',
        'file_transkrip_nilai',
        'file_persetujuan_penguji_semhas',
        'file_naskah_skripsi',
        'id_kategori_ta',
        'status',
        'alasan',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'id_mahasiswa')->withTrashed();
    }

    public function dosenPembimbingAkademik()
    {
        return $this->belongsTo(User::class, 'id_dosen_pembimbing_akademik');
    }

    public function pembimbing1()
    {
        return $this->belongsTo(User::class, 'id_dosen_pembimbing_1');
    }

    public function pembimbing2()
    {
        return $this->belongsTo(User::class, 'id_dosen_pembimbing_2');
    }

    public function calonPenguji1()
    {
        return $this->belongsTo(User::class, 'id_calon_penguji_1');
    }

    public function calonPenguji2()
    {
        return $this->belongsTo(User::class, 'id_calon_penguji_2');
    }

    public function kategoriTa()
    {
        return $this->belongsTo(KategoriTA::class, 'id_kategori_ta');
    }
}
