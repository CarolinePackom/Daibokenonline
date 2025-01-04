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
    ];

    public function ventes()
    {
        return $this->belongsToMany(Vente::class, 'vente_produit')
                    ->withPivot('quantite')
                    ->withTimestamps();
    }

}
