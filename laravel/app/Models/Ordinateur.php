<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use phpseclib3\Net\SSH2;

class Ordinateur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'adresse_ip',
        'adresse_mac',
        'est_allumé',
        'en_maintenance',
        'last_update',
    ];

    public function historiqueClients()
    {
        return $this->hasMany(HistoriqueOrdinateur::class);
    }

    protected function connexionSSH(): SSH2
    {
        $identifiant = "Admin";
        $mot_de_passe = "123Soleil-Daiboken";

        $ssh = new SSH2($this->adresse_ip);
        if (!$ssh->login($identifiant, $mot_de_passe)) {
            throw new Exception("Échec de la connexion SSH vers {$this->adresse_ip}");
        }
        return $ssh;
    }

    public function supprimerUtilisateur(string $nom_utilisateur):void
    {
        $ssh = $this->connexionSSH();

        try {
            $ssh->exec("net user \"{$nom_utilisateur}\" /delete");
        } finally {
            $ssh->disconnect();
        }
    }

    public function creerUtilisateur(string $nom_utilisateur): void
    {
        $ssh = $this->connexionSSH();

        try {
            // Supprime l'utilisateur s'il existe déjà
            $ssh->exec("net user \"{$nom_utilisateur}\" /delete");

            // Crée un nouvel utilisateur Windows
            $ssh->exec('net user "' . $nom_utilisateur . '" /add');

            // Vérifier si la session de l'utilisateur est active
            $script = <<<POWERSHELL
        \$UserToSwitch = "{$nom_utilisateur}"
        \$session = query user | Where-Object { \$_ -match \$UserToSwitch }

        if (\$session) {
            \$sessionID = (\$session -split '\\s+')[2]
            try {
                tscon \$sessionID /dest:console
            } catch {
                Write-Host "Failed to switch to user \$UserToSwitch. Error: \$_" -ForegroundColor Red
            }
        } else {
            Write-Host "User \$UserToSwitch does not have an active session." -ForegroundColor Yellow
            Write-Host "Please log in as \$UserToSwitch first."
        }
        POWERSHELL;

            // Exécuter le script PowerShell
            $ssh->exec("powershell -Command \"$script\"");

        } finally {
            $ssh->disconnect();
        }
    }


    public function clientActuel()
    {
        return $this->hasOne(Client::class, 'id', 'id')
            ->whereHas('historiqueOrdinateurs', function ($query) {
                $query->whereNull('fin_utilisation');
            });
    }

    public function eteindre(): void
    {
        $ssh = $this->connexionSSH();

        try {
            $ssh->exec("shutdown /s /f /t 0");
        } finally {
            $ssh->disconnect();
            sleep(5);
            $this->estEnLigne();
        }
    }

    public function allumer(): void
    {
        $broadcast = '192.168.1.255';
        $port = 9;

        $mac = str_replace([':', '-', '.'], '', $this->adresse_mac);
        if (strlen($mac) !== 12) {
            throw new Exception("Adresse MAC invalide.");
        }

        $packet = str_repeat(chr(0xFF), 6);
        for ($i = 0; $i < 16; $i++) {
            $packet .= pack("H*", $mac);
        }

        if (!$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
            throw new Exception("Erreur lors de la création de la socket.");
        }
        socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, true);

        $sent = socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, $port);
        if ($sent === false) {
            throw new Exception("Erreur lors de l'envoi du paquet WOL.");
        }

        socket_close($sock);
        sleep(5);
        $this->estEnLigne();
    }

    public function mettreAJour(): void
    {
        $ssh = $this->connexionSSH();

        try {
            $commande = 'powershell -Command "Install-Module -Name PSWindowsUpdate -Force; Import-Module PSWindowsUpdate; Install-WindowsUpdate -AcceptAll -AutoReboot"';
            $ssh->exec($commande);
        } finally {
            $ssh->disconnect();
            $this->update(['last_update' => now()]);
            sleep(5);
            $this->estEnLigne();
        }
    }

    public function estEnLigne(): bool
    {
        $timeout = 1;
        $fp = @fsockopen($this->adresse_ip, 22, $errno, $errstr, $timeout);
        if ($fp) {
            fclose($fp);
            $this->update(['est_allumé' => true]);
            return true;
        }
        $this->update(['est_allumé' => false]);
        return false;
    }

}
