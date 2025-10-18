<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaginaPrincipal extends Model
{
    protected $table = 'paginaprincipal';
    protected $fillable = ['titulo', 'descripcion', 'foto'];
}