<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
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

    protected function connexionSSH($identifiant='Admin', $mot_de_passe='123Soleil-Daiboken', $ip=null): SSH2
    {
        $ip = $ip ?? $this->adresse_ip;
        $ssh = new SSH2($ip);
        if (!$ssh->login($identifiant, $mot_de_passe)) {
            throw new Exception("Échec de la connexion SSH vers {$ip}");
        }
        return $ssh;
    }

    public function supprimerUtilisateur(string $nom_utilisateur): void
{
    $ssh = $this->connexionSSH();

    try {
        $nom_utilisateur = trim($nom_utilisateur);

        $ssh->exec("net user \"{$nom_utilisateur}\" /delete");

        $ssh->exec("for /f \"skip=1 tokens=3\" %i in ('query session') do logoff %i");
        $ssh->exec("logoff 1");

        $ssh->exec("if exist \"C:\\Users\\{$nom_utilisateur}\" rd /s /q \"C:\\Users\\{$nom_utilisateur}\"");

        $ssh->exec("reg delete \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v AutoAdminLogon /f");
        $ssh->exec("reg delete \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v DefaultUserName /f");
        $ssh->exec("reg delete \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v DefaultPassword /f");

        $ssh->exec("wmic path Win32_UserProfile where Name='C:\\\\Users\\\\{$nom_utilisateur}' delete");

    } finally {
        $ssh->disconnect();
    }
}

    public function creerUtilisateur(string $nom_utilisateur): void
{
    $ssh = $this->connexionSSH();

    $this->allumer();

    try {
        $nom_utilisateur = trim($nom_utilisateur);
        $nom_domaine = "WORKGROUP"; // Par défaut, la plupart des PC sont sous "WORKGROUP"

        // 1️⃣ Créer l'utilisateur et l'activer immédiatement
        $ssh->exec("net user \"{$nom_utilisateur}\" /add /active:yes /passwordreq:no /passwordchg:no");

        // 2️⃣ Configurer l'auto-login dans le registre
        $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v AutoAdminLogon /t REG_SZ /d \"1\" /f");
        $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v DefaultUserName /t REG_SZ /d \"{$nom_utilisateur}\" /f");
        $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v DefaultDomainName /t REG_SZ /d \"{$nom_domaine}\" /f"); // 🔥 Ajout clé manquante
        $ssh->exec("reg delete \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v DefaultPassword /f");

        // 3️⃣ Désactiver les restrictions sur les comptes sans mot de passe
        $ssh->exec("reg add \"HKLM\\SYSTEM\\CurrentControlSet\\Control\\Lsa\" /v LimitBlankPasswordUse /t REG_DWORD /d 0 /f");

        // 4️⃣ S'assurer que Windows ne bloque pas l'auto-login après un redémarrage
        $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v AutoLogonCount /t REG_DWORD /d 1 /f");

        // 5️⃣ Désactiver l’expérience OOBE pour éviter les écrans de configuration
        $ssh->exec("REG ADD HKLM\\SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\OOBE /v SkipUserOOBE /t REG_DWORD /d 1 /f");
        $ssh->exec("REG ADD HKLM\\SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\OOBE /v SkipMachineOOBE /t REG_DWORD /d 1 /f");
        $ssh->exec("REG ADD HKLM\\SOFTWARE\\Policies\\Microsoft\\Windows\\OOBE /v DisablePrivacyExperience /t REG_DWORD /d 1 /f");

        // 6️⃣ Redémarrer pour appliquer les changements
        $ssh->exec("shutdown /r /t 1");

        sleep(2);

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
    // Vérifier si un client est connecté
    $client = $this->clientActuel()->first();

    if ($client) {
        $client->deconnecterOrdinateur();
        sleep(5);
    }

    // Connexion SSH pour éteindre l'ordinateur
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
        $ip = trim(shell_exec("hostname -I | awk '{print $1}'"));

        $ssh = $this->connexionSSH('daiboken', '123Soleil-Daiboken', $ip);

        $mac = str_replace('-', ':', $this->adresse_mac);
        try {
            $ssh->exec("wakeonlan {$mac}");
        } finally {
            $ssh->disconnect();
            sleep(5);
            $this->estEnLigne();
        }
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
