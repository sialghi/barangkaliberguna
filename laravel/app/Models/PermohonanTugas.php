<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermohonanTugas extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_user',
        'deskripsi_surat', 'alasan_penolakan', 'status',
        'file_1', 'file_2', 'tanggal_ttd',
    ];

    public function dosen()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
