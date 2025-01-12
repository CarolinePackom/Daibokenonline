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
    ];

    public function decrementCredit(float $montant): void
    {
        $this->decrement('solde_credit', $montant);
    }

    public function incrementCredit(float $montant): void
    {
        $this->increment('solde_credit', $montant);
    }

    public function ordinateur()
    {
        return $this->hasOne(Ordinateur::class);
    }
}
