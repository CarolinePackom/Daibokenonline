<?php

namespace App\Models;

use App\Enums\StatutEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'statut',
        'est_paye',
        'moyen_paiement',
        'total',
        'nombre_credits',
        'formule_id',
        'nombre_heures',
        'nombre_jours',
    ];

    protected $casts = [
        'statut' => StatutEnum::class,
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'vente_produit')
                    ->withPivot('quantite')
                    ->withTimestamps();
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'vente_service')
                    ->withTimestamps();
    }

    public function formule()
    {
        return $this->belongsTo(Formule::class, 'formule_id');
    }
}
