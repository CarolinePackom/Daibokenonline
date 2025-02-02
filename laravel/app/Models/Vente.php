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
                if (!empty($data['nombre_credits']) && $data['nombre_credits'] > 0) {
                    $client->incrementCredit($data['nombre_credits']);
                }
                if ($vente->moyen_paiement === 'credit' && $client->solde_credit >= $vente->total) {
                    $client->decrementCredit($vente->total);
                }
            }

            // ✅ Ajout des produits
            $produitsToAttach = [];
            foreach ($data['produits'] as $produit) {
                if (!empty($produit['produit_id']) && Produit::where('id', $produit['produit_id'])->exists()) {
                    $produitsToAttach[$produit['produit_id']] = ['quantite' => $produit['quantite'] ?? 1];
                }
            }
            if (!empty($produitsToAttach)) {
                $vente->produits()->attach($produitsToAttach);
            }

            // ✅ Ajout des services
            if (!empty($data['service_ids'])) {
                $vente->services()->attach($data['service_ids']);
            }
        });
    }


    public static function prepareVenteData(array $data): array
    {
        $data['user_id'] = auth()->id();

        // 🔥 Vérification des IDs valides
        $data['service_ids'] = array_filter(
            isset($data['service_ids']) ? explode(',', $data['service_ids']) : [],
            fn($id) => is_numeric($id)
        );

        $data['produits'] = $data['produits'] ?? [];

        // ✅ Vérification et récupération de la formule
        $formuleId = !empty($data['formule_id']) && Formule::where('id', $data['formule_id'])->exists()
            ? $data['formule_id']
            : null;

        // ✅ Si une durée personnalisée est utilisée, elle prime sur une formule
        if (!empty($data['custom_duration']) && !empty($data['custom_unit'])) {
            $formuleId = null;
        }

        $data['formule_id'] = $formuleId;
        $data['nombre_heures'] = $data['custom_unit'] === 'heures' ? $data['custom_duration'] : null;
        $data['nombre_jours'] = $data['custom_unit'] === 'jours' ? $data['custom_duration'] : null;

        return $data;
    }


    public function getTotalAttribute(): float
{
    $total = 0;
    $totalCredit = 0;

    // Calcul du total des produits
    foreach ($this->produits as $produit) {
        $total += $produit->prix * $produit->pivot->quantite;
    }

    // Ajout du prix des services
    foreach ($this->services as $service) {
        $total += $service->prix;
    }

    // Ajout du prix de la formule sélectionnée
    if ($this->formule) {
        $total += $this->formule->prix;
    }

    // Gestion du cas où la durée et l'unité sont personnalisées
    if ($this->custom_duration && $this->custom_unit) {
        $tarif = \App\Models\Tarif::first();
        $prixUnitaire = $this->custom_unit === 'heures' ? $tarif->prix_une_heure : $tarif->prix_un_jour;
        $total += $this->custom_duration * $prixUnitaire;
    }

    // Calcul du coût des crédits demandés
    if ($this->nombre_credits > 0) {
        $creditsDemandes = (int) $this->nombre_credits;
        foreach (Credit::orderByDesc('montant')->get() as $palier) {
            if ($creditsDemandes >= $palier->montant) {
                $reduction = $palier->montant - $palier->prix;
                $totalCredit = $creditsDemandes - $reduction;
                break;
            }
        }

        if ($totalCredit > 0) {
            $total += $totalCredit;
        } else {
            $total += $creditsDemandes;
        }
    }

    return $total;
}
}
