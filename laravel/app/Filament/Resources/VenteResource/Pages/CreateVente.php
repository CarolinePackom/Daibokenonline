<?php

namespace App\Filament\Resources\VenteResource\Pages;

use App\Filament\Resources\VenteResource;
use App\Models\Client;
use App\Models\Produit;
use App\Models\Vente;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateVente extends CreateRecord
{
    use HasWizard;

    protected static string $resource = VenteResource::class;

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false),
            ])
            ->columns(null);
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Création')
                ->schema([
                    Section::make()
                        ->schema(
                            VenteResource::getCreationFormSchema()
                        )
                        ->columns(3),
                ]),

            Step::make('Paiement')
                ->schema([
                    Section::make()
                        ->schema([
                            VenteResource::getPaiementForm(),
                        ]),
                ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        $serviceIds = isset($data['service_ids']) ? explode(',', $data['service_ids']) : [];
        $serviceIds = array_filter($serviceIds, fn($id) => is_numeric($id)); // Filtrer les IDs numériques uniquement
        unset($data['service_ids']);

        $produits = $data['produits'] ?? [];
        unset($data['produits']);

        // Mise à jour des crédits du client
        if ($data['moyen_paiement'] === 'credit') {
            $client = Client::find($data['client_id']);
            if ($client && $client->solde_credit >= $data['total']) {
                $client->decrement('solde_credit', $data['total']);
            }
        }

        // Création de la vente
        $vente = Vente::create($data);

        foreach ($produits as $produit) {
    if (!empty($produit['produit_id']) && Produit::find($produit['produit_id'])) {
        $vente->produits()->attach($produit['produit_id'], [
            'quantite' => $produit['quantite'] ?? 1,
        ]);
    }
}


        // Ajout des services à la vente
        foreach ($serviceIds as $serviceId) {
            $vente->services()->attach((int) $serviceId); // Convertir chaque ID en entier
        }

        return $data;
    }

}
