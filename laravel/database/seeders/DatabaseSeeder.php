<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Client;
use App\Models\Credit;
use App\Models\Formule;
use App\Models\Menus\Accompagnement;
use App\Models\Menus\Sauce;
use App\Models\Menus\Supplement;
use App\Models\Menus\Taille;
use App\Models\Ordinateurs\Ordinateur;
use App\Models\Service;
use App\Models\Statut;
use App\Models\Tarif;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Mathis',
            'username' => 'mathis',
            'email' => 'malherbe.mathis@gmail.com',
            'password' => Hash::make('Onaimelekfc51'),
        ]);

        User::factory()->create([
            'name' => 'Marvin',
            'username' => 'marvin',
            'email' => 'marvin.wth@gmail.com',
            'password' => Hash::make('dbk'),
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
            'nom' => 'DBK-0',
            'adresse_ip' => '192.168.1.36',
            'adresse_mac' => '10-FF-E0-4B-09-89',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-1',
            'adresse_ip' => '192.168.1.17',
            'adresse_mac' => '74-56-3C-DB-93-32',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-2',
            'adresse_ip' => '192.168.1.18',
            'adresse_mac' => '74-56-3C-DB-93-45',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-3',
            'adresse_ip' => '192.168.1.19',
            'adresse_mac' => '74-56-3C-DB-93-34',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-4',
            'adresse_ip' => '192.168.1.20',
            'adresse_mac' => '74-56-3C-DB-93-35',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-5',
            'adresse_ip' => '192.168.1.21',
            'adresse_mac' => '74-56-3C-DB-8F-1F',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-6',
            'adresse_ip' => '192.168.1.22',
            'adresse_mac' => '74-56-3C-DB-8F-23',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-7',
            'adresse_ip' => '192.168.1.23',
            'adresse_mac' => '74-56-3C-DB-8C-CE',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-8',
            'adresse_ip' => '192.168.1.24',
            'adresse_mac' => '74-56-3C-DB-8F-17',
            'last_update' => now(),
        ]);

        Ordinateur::create([
            'nom' => 'DBK-9',
            'adresse_ip' => '192.168.1.25',
            'adresse_mac' => '74-56-3C-DB-93-0C',
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
            'prenom' => 'Mathis',
            'nom' => 'Malherbe',
            'email' => 'malherbe.mathis@gmail.com',
            'id_nfc' => '0422BEEA157480',
        ]);

        Client::create([
            'prenom' => 'Marvin',
            'nom' => 'Wirth',
            'email' => 'marvin.wth@gmail.Com',
        ]);

        Taille::create([
            'nom' => 'Normal',
            'prix' => 7.5,
        ]);

        Taille::create([
            'nom' => 'XL',
            'prix' => 10,
        ]);

        Sauce::create([
            'nom' => 'La Shōnen',
            'description' => '🔥 La sauce des guerriers, ultra épicée',
            'prix_supplementaire' => 0,
        ]);

        Sauce::create([
            'nom' => 'La Sensei',
            'description' => '🥥 La sauce des sages, une douce sauce curry coco',
            'prix_supplementaire' => 0,
        ]);

        Accompagnement::create([
            'nom' => 'Boeuf',
            'prix_supplementaire' => 0,
        ]);

        Accompagnement::create([
            'nom' => 'Poulet',
            'prix_supplementaire' => 0,
        ]);

        Accompagnement::create([
            'nom' => 'Crevettes',
            'prix_supplementaire' => 0,
        ]);

        Supplement::create([
            'nom' => 'Oeuf',
            'prix' => 1,
        ]);

        Supplement::create([
            'nom' => 'Fromage',
            'prix' => 1,
        ]);
    }
}
