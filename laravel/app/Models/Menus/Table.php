<?php

namespace App\Models\Menus;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'numero_table_id',
        ];

    public function numeroTable()
    {
        return $this->belongsTo(NumeroTable::class);
    }

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
}
