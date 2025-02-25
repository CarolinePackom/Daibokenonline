<?php

namespace App\Models\Menus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumeroTable extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'numero',
        ];

    public function tables()
    {
        return $this->hasMany(Table::class);
    }
}
