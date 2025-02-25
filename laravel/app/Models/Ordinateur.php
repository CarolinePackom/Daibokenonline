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
        $nom_utilisateur = trim($nom_utilisateur);

        // 🔹 Supprime l'utilisateur s'il existe déjà
        $ssh->exec("net user \"{$nom_utilisateur}\" /delete");

        // 🔹 Crée un nouvel utilisateur Windows sans mot de passe et sans demande de changement
        $ssh->exec("net user \"{$nom_utilisateur}\" /add /active:yes /passwordreq:no /passwordchg:no");

        // 🔹 Désactiver l'expérience OOBE et autres paramètres initiaux
        $ssh->exec("powershell -Command \"REG ADD 'HKLM\\Software\\Microsoft\\Windows\\CurrentVersion\\OOBE' /v SkipUserOOBE /t REG_DWORD /d 1 /f\"");
        $ssh->exec("powershell -Command \"REG ADD 'HKLM\\Software\\Microsoft\\Windows\\CurrentVersion\\OOBE' /v SkipMachineOOBE /t REG_DWORD /d 1 /f\"");
        $ssh->exec("powershell -Command \"REG ADD 'HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\Explorer\\Advanced' /v EnableBalloonTips /t REG_DWORD /d 0 /f\"");
        $ssh->exec("powershell -Command \"REG ADD 'HKCU\\Software\\Policies\\Microsoft\\Windows\\CloudContent' /v DisableWindowsConsumerFeatures /t REG_DWORD /d 1 /f\"");
        $ssh->exec("powershell -Command \"REG ADD 'HKCU\\Software\\Microsoft\\Windows\\CurrentVersion\\UserProfileEngagement' /v ScoobeSystemSettingEnabled /t REG_DWORD /d 0 /f\"");

        // 🔹 Vérifier si la session de l'utilisateur est active
        $ssh->exec("powershell -Command \"query user | Where-Object { \$_ -match '{$nom_utilisateur}' } > C:\\session_result.txt\"");

        // 🔹 Récupérer l'ID de session
        $session_id_result = $ssh->exec("powershell -Command \"(Get-Content C:\\session_result.txt) -match '{$nom_utilisateur}'\"");

        // 🔹 Vérifier si une session a été trouvée
        if (trim($session_id_result) !== '') {
            // Extraire l'ID de session
            $session_id = trim(explode(" ", preg_replace('/\s+/', ' ', $session_id_result))[2]);

            // 🔹 Se connecter à la session utilisateur
            $ssh->exec("powershell -Command \"tscon {$session_id} /dest:console\"");
        } else {
            // Aucun utilisateur trouvé, afficher un message
            $ssh->exec("powershell -Command \"Write-Host 'Utilisateur non trouvé ou non connecté'\"");
        }

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
