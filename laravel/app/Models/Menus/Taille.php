<?php

namespace App\Models\Menus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taille extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'nom',
            'prix',
        ];
}
