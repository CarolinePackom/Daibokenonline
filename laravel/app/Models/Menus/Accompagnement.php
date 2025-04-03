<?php

namespace App\Models\Menus;

use Illuminate\Database\Eloquent\Model;

class Accompagnement extends Model
{
    protected $fillable = [
        'nom',
        'prix_supplementaire',
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

}
