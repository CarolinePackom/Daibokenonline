<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formule extends Model
{
    protected $fillable = [
        'nom',
        'duree_en_heures',
        'duree_en_jours',
        'prix',
    ];

}
