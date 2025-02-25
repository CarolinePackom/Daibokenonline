<?php

namespace App\Models\Menus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accompagnement extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'nom',
            'prix_supplementaire',
        ];
}
