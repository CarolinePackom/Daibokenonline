<?php

use App\Models\Ordinateurs\Ordinateur;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use phpseclib3\Net\SSH2;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    Ordinateur::verifierTousEnLigne();
})->everyTenSeconds();

Schedule::call(function () {
    $ssh = new SSH2('192.168.1.28');
    if (!$ssh->login('daiboken', '123Soleil-Daiboken')) {
        throw new Exception("Échec de la connexion SSH vers 192.168.1.28");
    }
    $ssh->exec('echo "123Soleil-Daiboken" | sudo -S systemctl start nfcreader.service');
})->everyTwoMinutes();
