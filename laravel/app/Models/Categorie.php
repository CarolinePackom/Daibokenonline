<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $fillable = [
        'nom',
        'icone',
    ];

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'categorie_produit')->withTimestamps();
    }

}
