<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Identifiant extends Model
{
    protected $fillable = [
        'identifiant',
        'mot_de_passe',
    ];

    public static function getGlobal()
    {
        return self::first();
    }
}
