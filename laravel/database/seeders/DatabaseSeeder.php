<?php

namespace Database\Seeders;

use App\Enums\StatutEnum;
use App\Models\Categorie;
use App\Models\Client;
use App\Models\Credit;
use App\Models\Formule;
use App\Models\Ordinateur;
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

        Ordinateur::create([
            'nom' => 'DBK-1',
            'adresse_ip' => '192.168.1.1',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-2',
            'adresse_ip' => '192.168.1.2',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-3',
            'adresse_ip' => '192.168.1.3',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-4',
            'adresse_ip' => '192.168.1.4',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-5',
            'adresse_ip' => '192.168.1.5',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-6',
            'adresse_ip' => '192.168.1.6',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-7',
            'adresse_ip' => '192.168.1.7',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-8',
            'adresse_ip' => '192.168.1.8',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-9',
            'adresse_ip' => '192.168.1.9',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-10',
            'adresse_ip' => '192.168.1.10',
            'last_update' => now(),
        ]);

        Categorie::create([
            'nom' => 'Sodas',
            'icone' => 'fas-bottle-water'
        ]);

        Categorie::create([
            'nom' => 'Boissons chaudes',
            'icone' => 'fas-mug-hot'
        ]);

        Categorie::create([
            'nom' => 'Végans',
            'icone' => 'fas-seedling'
        ]);

        Categorie::create([
            'nom' => 'Pimentés',
            'icone' => 'fas-pepper-hot'
        ]);

        Categorie::create([
            'nom' => 'Desserts',
            'icone' => 'fas-ice-cream'
        ]);

        Categorie::create([
            'nom' => 'Snacks',
            'icone' => 'fas-cookie'
        ]);

        Categorie::create([
            'nom' => 'Fruits',
            'icone' => 'fas-apple-whole'
        ]);

        Categorie::create([
            'nom' => 'Poissons',
            'icone' => 'fas-fish'
        ]);

        Categorie::create([
            'nom' => 'Fruits de mer',
            'icone' => 'fas-shrimp'
        ]);

        Categorie::create([
            'nom' => 'Poulets',
            'icone' => 'fas-drumstick-bite'
        ]);

        Categorie::create([
            'nom' => 'Légumes',
            'icone' => 'fas-carrot'
        ]);

        Categorie::create([
            'nom' => 'Fromages',
            'icone' => 'fas-cheese'
        ]);

        Categorie::create([
            'nom' => 'Charcuteries',
            'icone' => 'fas-bacon'
        ]);

        Categorie::create([
            'nom' => 'Bonbons',
            'icone' => 'fas-candy-cane'
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
