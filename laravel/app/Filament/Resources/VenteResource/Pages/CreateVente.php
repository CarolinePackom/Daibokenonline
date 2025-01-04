<?php

namespace App\Filament\Resources\VenteResource\Pages;

use App\Filament\Resources\VenteResource;
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
                            VenteResource::getDetailsFormSchema()
                        )
                        ->columns(),
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
        // Pré-remplir le client ID
        $data['client_id'] = request()->get('client_id');

        // Ajouter le produit prédéfini si le paramètre est présent
        $produitId = request()->get('produit_id');
        if ($produitId) {
            $produit = \App\Models\Produit::find($produitId);
            if ($produit) {
                $data['produits'] = [
                    [
                        'produit_id' => $produitId,
                        'quantite' => 1,
                        'prix' => $produit->prix,
                    ],
                ];
            }
        }

        return $data;
    }
}
