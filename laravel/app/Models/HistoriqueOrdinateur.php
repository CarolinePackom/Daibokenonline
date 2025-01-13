<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueOrdinateur extends Model
{
    use HasFactory;

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
