<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\NilaiSkripsi;
use App\Models\NilaiSemhas;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'nim_nip_nidn',
        'email', 'password',
        'email_verified_at', 'ttd',
        'alt_email', 'no_hp',
        'jalur_masuk'
    ];

    protected $with =  [
        'pivot', 'roles', 'ProgramStudi', 'fakultas'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function nilaiSkripsi()
    {
        return $this->hasOne(NilaiSkripsi::class, 'id_user');
    }

    public function nilaiSemhas()
    {
        return $this->hasOne(NilaiSemhas::class, 'id_user');
    }

    public function pendaftaranSempro()
    {
        return $this->hasOne(PendaftaranSempro::class, 'id_mahasiswa');
    }

    public function nilaiSempro()
    {
        return $this->hasOne(NilaiSempro::class, 'id_mahasiswa');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_pivot', 'id_user', 'id_role');
    }

    public function programStudi()
    {
        return $this->belongsToMany(ProgramStudi::class, 'users_pivot', 'id_user', 'id_program_studi');
    }

    public function fakultas()
    {
        return $this->belongsToMany(Fakultas::class, 'users_pivot', 'id_user', 'id_fakultas');
    }

    public function pivot()
    {
        return $this->hasMany(UsersPivot::class, 'id_user');
    }
}
