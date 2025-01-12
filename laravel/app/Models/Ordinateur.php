<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ordinateur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'en_service',
        'client_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
