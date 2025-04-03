<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    protected $fillable = [
        'prix_une_heure',
        'prix_un_jour',
    ];
}
