<?php

namespace App\Models\Menus;

use Illuminate\Database\Eloquent\Model;

class Taille extends Model
{
    protected $fillable = [
        'nom',
        'prix',
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
}
