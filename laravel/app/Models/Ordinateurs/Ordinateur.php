<?php

namespace App\Models\Ordinateurs;

use App\Models\Identifiant;
use Exception;
use Illuminate\Database\Eloquent\Model;
use phpseclib3\Net\SSH2;

class Ordinateur extends Model
{
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

    protected function connexionSSH($identifiant=null, $mot_de_passe=null, $ip=null): SSH2
    {
        $ip = $ip ?? $this->adresse_ip;
        if (!$identifiant || !$mot_de_passe) {
            $global = Identifiant::getGlobal();

            $identifiant = $identifiant ?? $global->identifiant;
            $mot_de_passe = $mot_de_passe ?? $global->mot_de_passe;
        }

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

            $ssh->exec("reg delete \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v AutoAdminLogon /f");
            $ssh->exec("reg delete \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v DefaultUserName /f");
            $ssh->exec("reg delete \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v DefaultPassword /f");

            $ssh->exec("rd /s /q \"C:\\Users\\{$nom_utilisateur}\"");

        } finally {
            $ssh->disconnect();
        }
    }

    public function creerUtilisateur(string $nom_utilisateur): void
    {
        $ssh = $this->connexionSSH();

        $nom_utilisateur = trim($nom_utilisateur);
        $nom_domaine = "WORKGROUP";

        try {
            $ssh->exec("net user \"{$nom_utilisateur}\" /add /active:yes /passwordreq:no /passwordchg:no");

            $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v AutoAdminLogon /t REG_SZ /d \"1\" /f");
            $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v DefaultUserName /t REG_SZ /d \"{$nom_utilisateur}\" /f");
            $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v DefaultPassword /t REG_SZ /d \"\" /f");
            $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v DefaultDomainName /t REG_SZ /d \"{$nom_domaine}\" /f");

            $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows NT\\CurrentVersion\\Winlogon\" /v AutoLogonCount /t REG_DWORD /d 0xffffffff /f");

            $ssh->exec("reg add \"HKLM\\SYSTEM\\CurrentControlSet\\Control\\Lsa\" /v LimitBlankPasswordUse /t REG_DWORD /d 0 /f");

            $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\OOBE\" /v SkipUserOOBE /t REG_DWORD /d 1 /f");
            $ssh->exec("reg add \"HKLM\\SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\OOBE\" /v SkipMachineOOBE /t REG_DWORD /d 1 /f");
            $ssh->exec("reg add \"HKLM\\SOFTWARE\\Policies\\Microsoft\\Windows\\OOBE\" /v DisablePrivacyExperience /t REG_DWORD /d 1 /f");

            $ssh->exec("reg add \"HKLM\\SOFTWARE\\Policies\\Microsoft\\Windows\\Personalization\" /v NoLockScreen /t REG_DWORD /d 1 /f");

            sleep(2);

            $ssh->exec("shutdown /r /t 1");
        } finally {
            $ssh->disconnect();
        }
    }


    public function clientActuel()
    {
        return $this->hasOne(HistoriqueOrdinateur::class, 'ordinateur_id')
            ->whereNull('fin_utilisation')
            ->with('client');
    }


    public function eteindre(): void
    {
        $historique = $this->clientActuel()->first();
        if ($historique && $historique->client) {
            $historique->client->deconnecterOrdinateur();
            sleep(5);
        }

        try {
            $ssh = $this->connexionSSH();
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'extinction de l'ordinateur {$this->adresse_ip}: " . $e->getMessage());
            return;
        }

        try {
            $ssh->exec("shutdown /s /f /t 0");
        } finally {
            $ssh->disconnect();
        }
    }

    public function allumer(): void
    {
        try {
            $ssh = $this->connexionSSH('daiboken', '123Soleil-Daiboken', '192.168.1.28');
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'allumage de l'ordinateur {$this->adresse_ip}: " . $e->getMessage());
            return;
        }

        $mac = str_replace('-', ':', $this->adresse_mac);
        try {
            $ssh->exec("wakeonlan {$mac}");
        } finally {
            $ssh->disconnect();
        }
    }

    public static function verifierTousEnLigne()
    {
        $ordinateurs = self::all();
        $resultats = [];

        foreach ($ordinateurs as $ordinateur) {
            $fp = @fsockopen($ordinateur->adresse_ip, 22, $errno, $errstr, 1);

            if ($fp) {
                fclose($fp);
                $resultats[$ordinateur->id] = true;
            } else {
                $resultats[$ordinateur->id] = false;
            }
        }

        self::whereIn('id', array_keys(array_filter($resultats, fn($v) => $v)))->update(['est_allumé' => true]);
        self::whereNotIn('id', array_keys(array_filter($resultats, fn($v) => $v)))->update(['est_allumé' => false]);
    }

    public function mettreAJour(): void
    {
        try {
            $ssh = $this->connexionSSH();
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la mise à jour de l'ordinateur {$this->adresse_ip}: " . $e->getMessage());
            return;
        }

        try {
            $ssh->exec("echo '=== Démarrage des mises à jour Windows ===' > C:\\update_log.txt");
            $ssh->exec("usoclient StartScan >> C:\\update_log.txt 2>&1");
            sleep(5);
            $ssh->exec("usoclient StartInstall >> C:\\update_log.txt 2>&1");
            sleep(10);

            $rebootRequired = $ssh->exec("if exist C:\\Windows\\WinSxS\\pending.xml (echo 'REBOOT_NEEDED')");
            $ssh->exec("echo '=== Mise à jour des pilotes ===' >> C:\\update_log.txt");
            $ssh->exec("pnputil /scan-devices >> C:\\update_log.txt 2>&1");

            $driversList = $ssh->exec("pnputil /enum-drivers | findstr 'oem'");
            $driversArray = explode("\n", trim($driversList));
            foreach ($driversArray as $driver) {
                preg_match('/oem\d+\.inf/', $driver, $matches);
                if (!empty($matches[0])) {
                    $ssh->exec("pnputil /update-driver {$matches[0]} /force >> C:\\update_log.txt 2>&1");
                }
            }

            $driverReboot = $ssh->exec("wmic qfe list brief | findstr /i 'Reboot'");
            if (str_contains($rebootRequired, 'REBOOT_NEEDED') || !empty($driverReboot)) {
                $ssh->exec("shutdown /r /t 60");
            }
        } finally {
            $ssh->disconnect();
            $this->update(['last_update' => now()]);
        }
    }

    public function changerMotDePasseLocal(string $ancienMdp, string $nouveauMdp): void
    {
        try {
            $ssh = $this->connexionSSH(null, $ancienMdp);
        } catch (\Exception $e) {
            \Log::warning("Connexion échouée à {$this->adresse_ip} pour changer le mot de passe : " . $e->getMessage());
            return;
        }

        try {
            $identifiant = \App\Models\Identifiant::getGlobal()->identifiant;
            $ssh->exec("net user \"{$identifiant}\" \"{$nouveauMdp}\"");
        } finally {
            $ssh->disconnect();
        }
    }

}
