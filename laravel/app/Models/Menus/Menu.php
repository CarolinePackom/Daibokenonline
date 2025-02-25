<?php

namespace App\Models\Menus;

use App\Models\Client;
use App\Models\Produit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'statut',
        'credit',
        'note',
        'prix',
        'prix_total',
        'sauce_id',
        'accompagnement_id',
        'taille_id',
        'client_id',
        'table_id',
    ];

    public function sauce()
    {
        return $this->belongsTo(Sauce::class);
    }

    public function accompagnement()
    {
        return $this->belongsTo(Accompagnement::class);
    }

    public function taille()
    {
        return $this->belongsTo(Taille::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'menu_produit');
    }

    public function supplements()
    {
        return $this->belongsToMany(Supplement::class, 'menu_supplement');
    }
}
