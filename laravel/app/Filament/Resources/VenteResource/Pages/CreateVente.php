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

}
