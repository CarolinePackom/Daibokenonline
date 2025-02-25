<?php

namespace App\Models\Menus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sauce extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'nom',
            'description',
            'prix_supplementaire',
        ];
}
