<?php

namespace App\Models\Menus;

use Illuminate\Database\Eloquent\Model;

class Supplement extends Model
{
    protected $fillable = [
        'nom',
        'prix',
    ];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_supplement');
    }
}
