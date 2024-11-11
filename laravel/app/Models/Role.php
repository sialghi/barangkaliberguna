<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'nama',
    ];

    // protected $with = ['programStudi'];

    // protected $hidden = ['pivot'];

    public function user()
    {
        return $this->hasManyThrough(User::class, UsersPivot::class, 'id_role', 'id_user');
    }

    public function programStudi()
    {
        return $this->hasOneThrough(ProgramStudi::class, UsersPivot::class);
    }

    public function fakultas()
    {
        return $this->hasManyThrough(Fakultas::class, UsersPivot::class, 'id_role', 'id_fakultas');
    }
}
