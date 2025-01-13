<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'est_present',
        'id_nfc',
        'solde_credit',
        'archived_at',
        'ordinateur_id',
    ];

    public function decrementCredit(float $montant): void
    {
        $this->decrement('solde_credit', $montant);
    }

    public function incrementCredit(float $montant): void
    {
        $this->increment('solde_credit', $montant);
    }

    public function historiqueOrdinateurs()
    {
        return $this->hasMany(HistoriqueOrdinateur::class);
    }

    public function connecterOrdinateur(int $ordinateurId): void
    {
        $ordinateur = Ordinateur::findOrFail($ordinateurId);

        if ($ordinateur->en_maintenance || !$ordinateur->est_allumé) {
            throw new \Exception('Ordinateur indisponible.');
        }

        HistoriqueOrdinateur::create([
            'client_id' => $this->id,
            'ordinateur_id' => $ordinateurId,
            'debut_utilisation' => now(),
        ]);
    }

    public function deconnecterOrdinateur(): void
    {
        $historique = HistoriqueOrdinateur::where('client_id', $this->id)
            ->whereNull('fin_utilisation')
            ->first();

        if ($historique) {
            $historique->update(['fin_utilisation' => now()]);
        }
    }


}
