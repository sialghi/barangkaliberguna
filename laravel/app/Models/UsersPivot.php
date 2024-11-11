<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsersPivot extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'users_pivot';

    protected $fillable = [
        'id_user', 'id_role', 'id_program_studi', 'id_fakultas'
    ];

    protected $hidden = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_program_studi');
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'id_fakultas');
    }
}
