<?php

namespace App\Models;

use App\Enums\StatutEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vente extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'statut',
        'moyen_paiement',
        'total',
        'nombre_credits',
        'formule_id',
        'nombre_heures',
        'nombre_jours',
    ];

    protected $casts = [
        'statut' => StatutEnum::class,
        'nombre_credit' => 'float',
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

    public static function createVente(Vente $vente, array $data): void
{
    DB::transaction(function () use ($vente, $data) {
        // 🔥 Gestion des crédits
        $client = Client::find($vente->client_id);
        if ($client) {
            if (isset($data['nombre_credits'])) {
                if ($data['nombre_credits'] > 0) {
                    // L'utilisateur achète des crédits : on incrémente son solde.
                    $client->incrementCredit($data['nombre_credits']);
                } elseif ($data['nombre_credits'] < 0) {
                    // L'utilisateur utilise ses crédits pour payer.
                    // On prend la valeur absolue du montant à utiliser.
                    $creditToUse = abs($data['nombre_credits']);
                    // Optionnel : vérifier que le client a suffisamment de crédits
                    if ($client->solde_credit >= $creditToUse) {
                        $client->decrementCredit($creditToUse);
                    } else {
                        // Si le client n'a pas assez de crédits, on décrémente tout son solde.
                        $client->decrementCredit($client->solde_credit);
                    }
                }
            }
        }

        // ✅ Correction du total pour s'assurer qu'il est bien un `float`
        $vente->total = (float) str_replace([',', '€'], ['.', ''], $data['total']);
        $vente->save();

        // ✅ Ajout des produits vendus et mise à jour des stocks
        $produitsToAttach = [];
        foreach ($data['produits'] as $produit) {
            if (!empty($produit['produit_id']) && Produit::where('id', $produit['produit_id'])->exists()) {
                $quantity = $produit['quantite'] ?? 1;
                $produitsToAttach[$produit['produit_id']] = ['quantite' => $quantity];
            }
        }
        if (!empty($produitsToAttach)) {
            // Attachement des produits à la vente (enregistre les quantités dans la table pivot)
            $vente->produits()->attach($produitsToAttach);
            // Mise à jour des stocks pour chaque produit vendu
            foreach ($produitsToAttach as $produitId => $pivotData) {
                $quantity = $pivotData['quantite'];
                $produit = Produit::find($produitId);
                if ($produit) {
                    $produit->decrement('quantite_stock', $quantity);
                }
            }
        }

        // ✅ Ajout des services associés à la vente
        if (!empty($data['service_ids'])) {
            $vente->services()->attach($data['service_ids']);
        }
    });
}


    public static function prepareVenteData(array $data): array
{
    $data['user_id'] = auth()->id();

    // Convertir en float pour conserver le signe négatif le cas échéant
    $data['nombre_credits'] = isset($data['nombre_credits']) ? (float) $data['nombre_credits'] : 0;

    // Vérification des IDs valides pour service_ids
    $data['service_ids'] = array_filter(
        isset($data['service_ids']) ? explode(',', $data['service_ids']) : [],
        fn($id) => is_numeric($id)
    );

    $data['produits'] = $data['produits'] ?? [];

    // Vérification et récupération de la formule
    $formuleId = (!empty($data['formule_id']) && Formule::where('id', $data['formule_id'])->exists())
        ? $data['formule_id']
        : null;

    // Si une durée personnalisée est utilisée, elle prime sur une formule
    if (!empty($data['custom_duration']) && !empty($data['custom_unit'])) {
        $formuleId = null;
    }
    $data['formule_id'] = $formuleId;
    $data['nombre_heures'] = (isset($data['custom_duration']) && $data['custom_unit'] === 'heures')
        ? (int) $data['custom_duration']
        : null;
    $data['nombre_jours'] = (isset($data['custom_duration']) && $data['custom_unit'] === 'jours')
        ? (int) $data['custom_duration']
        : null;

    // ✅ Correction du total (suppression du symbole €, conversion correcte)
    $data['total'] = isset($data['total'])
        ? (float) str_replace([',', '€'], ['.', ''], $data['total']) // Convertit "0,00 €" en "0.00"
        : 0.00;

    return $data;
}





}
