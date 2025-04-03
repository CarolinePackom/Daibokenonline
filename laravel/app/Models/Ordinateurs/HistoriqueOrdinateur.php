<?php

namespace App\Models\Ordinateurs;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;

class HistoriqueOrdinateur extends Model
{
    protected $fillable = [
        'client_id',
        'ordinateur_id',
        'debut_utilisation',
        'fin_utilisation',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function ordinateur()
    {
        return $this->belongsTo(Ordinateur::class);
    }
}
