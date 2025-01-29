<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Notifications\NfcWelcomeNotification;
use Filament\Notifications\Actions\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Filament\Notifications\Notification;


class NfcController extends Controller
{
    public function scan(Request $request)
    {
        $idNfc = $request->input('id_nfc');
        $client = Client::where('id_nfc', $idNfc)->first();

        if ($client) {
            $this->clientNfc($client);
        } else {
            $this->aucunClientNfc($idNfc);
        }
    }

    private function clientNfc($client)
    {
        $wasPresent = $client->est_present;
        $client->update(['est_present' => !$client->est_present]);

        if ($wasPresent) {
            $client->deconnecterOrdinateur();
        }
        else {
            $this->stockageCache('dernier_client_nfc', [
                'prenom' => $client->prenom,
            ]);
        }
    }


    private function aucunClientNfc($IdNfc){
        $this->stockageCache('dernier_id_nfc', $IdNfc);
        $this->notificationAucunClientNfc($IdNfc);
    }

    private function notificationAucunClientNfc($IdNfc){
        $recipients = User::all();

        Notification::make()
            ->title('Carte NFC non attribuée')
            ->body("La carte NFC avec l'ID : $IdNfc n'est associée à aucun client.")
            ->warning()
            ->actions([
                Action::make('Créer un client')
                    ->url(route('filament.admin.resources.clients.create', ['id_nfc' => $IdNfc]))
                    ->button(),
            ])
            ->broadcast($recipients);
    }

    private function stockageCache(string $key, $value){
        Cache::put($key, $value, 10);
    }

    public function recupererDernierClient()
    {
        $this->startSse('dernier_client_nfc', function ($cachedData) {
            return [
                'prenom' => $cachedData['prenom'],
            ];
        });
    }

    public function recupererDernierIdNfc()
    {
        $cachedData = Cache::pull('dernier_id_nfc');

        if ($cachedData) {
            return response()->json(['id_nfc' => $cachedData], 200);
        }

        return response()->json(null, 204);
    }

    private function startSse(string $cacheKey, callable $formatter)
    {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        while (true) {
            if (connection_aborted()) {
                break;
            }

            $cachedData = Cache::pull($cacheKey); // Récupération et suppression

            if ($cachedData) {
                echo "data: " . json_encode($formatter($cachedData)) . "\n\n";
            } else {
                // Envoyer un ping pour garder la connexion active
                echo "event: ping\n";
                echo "data: {}\n\n";
            }

            @ob_flush();
            @flush();

            usleep(500000); // Pause pour limiter la charge serveur
        }

        echo "event: close\n";
        echo "data: {}\n\n";
        @ob_flush();
        @flush();
        exit;
    }


}
