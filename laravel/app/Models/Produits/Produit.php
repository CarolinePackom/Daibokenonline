<?php

namespace App\Models\Produits;

use App\Models\Categorie;
use App\Models\Vente;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Produit extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'nom',
        'prix',
        'en_vente',
        'quantite_stock',
        'seuil_quantite_alerte',
        'description',
    ];

    public function estEnAlerteStock()
    {
        return $this->quantite_stock <= $this->seuil_quantite_alerte;
    }

    public function categories()
    {
        return $this->belongsToMany(Categorie::class, 'categorie_produit')->withTimestamps();
    }


    public function ventes()
    {
        return $this->belongsToMany(Vente::class, 'vente_produit')
                    ->withPivot('quantite')
                    ->withTimestamps();
    }

}
