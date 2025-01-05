<?php

namespace Database\Seeders;

use App\Enums\StatutEnum;
use App\Models\Client;
use App\Models\Credit;
use App\Models\Formule;
use App\Models\Service;
use App\Models\Statut;
use App\Models\Tarif;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@daiboken.fr',
            'password' => Hash::make('admin'),
        ]);

        Service::create([
            'nom' => "Frais d'inscription",
            'prix' => 2.5,
        ]);

        Service::create([
            'nom' => "Création carte NFC",
            'prix' => 2.5,
        ]);

        Tarif::firstOrCreate([], [
            'prix_une_heure' => 1.5,
            'prix_un_jour' => 5,
        ]);

        Formule::create([
            'nom' => '2 Heures',
            'duree_en_heures' => 2,
            'prix' => 2.5,
        ]);

        Formule::create([
            'nom' => '1/2 Journée',
            'duree_en_heures' => 4,
            'prix' => 4.5,
        ]);

        Formule::create([
            'nom' => '1 Journée',
            'duree_en_jours' => 1,
            'prix' => 7.5,
        ]);

        Formule::create([
            'nom' => '1 Semaine',
            'duree_en_jours' => 7,
            'prix' => 30,
        ]);

        Credit::create([
            'montant' => 10,
            'prix' => 9,
        ]);

        Credit::create([
            'montant' => 25,
            'prix' => 22,
        ]);

        Credit::create([
            'montant' => 50,
            'prix' => 45,
        ]);

        Credit::create([
            'montant' => 100,
            'prix' => 80,
        ]);

        Credit::create([
            'montant' => 250,
            'prix' => 200,
        ]);

        Client::create([
            'nom' => 'Nom',
            'prenom' => 'Prénom',
            'email' => 'test@gmail.com',
            'telephone' => '0123456789',
            'est_present' => false,
            'id_nfc' => '1234567890',
            'solde_credit' => 0,
        ]);
    }
}
