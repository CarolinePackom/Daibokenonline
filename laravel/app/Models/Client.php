<?php

namespace App\Models;

use App\Services\WindowsService;
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
    $nouvelOrdinateur = Ordinateur::findOrFail($ordinateurId);

    if ($nouvelOrdinateur->en_maintenance || !$nouvelOrdinateur->est_allumé) {
        throw new \Exception('Ordinateur indisponible.');
    }

    // ✅ Déconnecter l'utilisateur de son ancien ordinateur s'il en a un
    $historique = HistoriqueOrdinateur::where('client_id', $this->id)
        ->whereNull('fin_utilisation')
        ->first();

    if ($historique) {
        $this->deconnecterOrdinateur();
    }

    $nouvelOrdinateur->creerUtilisateur($this->prenom . " " . $this->nom);

    HistoriqueOrdinateur::create([
        'client_id' => $this->id,
        'ordinateur_id' => $ordinateurId,
        'debut_utilisation' => now(),
    ]);

    $this->refresh();
}


    public function deconnecterOrdinateur(): void
    {
        $historique = HistoriqueOrdinateur::where('client_id', $this->id)
            ->whereNull('fin_utilisation')
            ->first();

        if ($historique) {
            // Récupérer l'ordinateur du client
            $ordinateur = Ordinateur::find($historique->ordinateur_id);

            if ($ordinateur) {
                // Supprimer l'utilisateur Windows
                $nom_utilisateur = $this->prenom . " " . $this->nom;
                $ordinateur->supprimerUtilisateur($nom_utilisateur);
            }

            // Mettre à jour l'historique et la base de données
            $historique->update(['fin_utilisation' => now()]);
        }
    }

}
