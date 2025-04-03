<?php

namespace App\Models\Menus;

use Illuminate\Database\Eloquent\Model;

class NumeroTable extends Model
{
    protected $fillable = [
        'numero',
    ];

    public function tables()
    {
        return $this->hasMany(Table::class);
    }
}
