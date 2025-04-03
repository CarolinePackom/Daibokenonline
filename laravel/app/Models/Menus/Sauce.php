<?php

namespace App\Models\Menus;

use Illuminate\Database\Eloquent\Model;

class Sauce extends Model
{
    protected $fillable = [
        'nom',
        'description',
        'prix_supplementaire',
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
}
