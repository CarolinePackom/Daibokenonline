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
    return $this->hasMany(Produit::class, 'categorie_id');
}

}
