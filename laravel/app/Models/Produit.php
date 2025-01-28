<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Produit extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'nom',
        'prix',
        'en_vente',
        'categorie_id',
        'quantite_stock',
        'seuil_quantite_alerte',
    ];

    public function estEnAlerteStock()
    {
        return $this->quantite_stock <= $this->seuil_quantite_alerte;
    }

    public function categorie()
{
    return $this->belongsTo(Categorie::class, 'categorie_id');
}


    public function ventes()
    {
        return $this->belongsToMany(Vente::class, 'vente_produit')
                    ->withPivot('quantite')
                    ->withTimestamps();
    }

}
