<?php

namespace App\Filament\Resources\OrdinateurResource\Pages;

use App\Filament\Resources\OrdinateurResource;
use App\Models\Ordinateur;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdinateur extends EditRecord
{
    protected static string $resource = OrdinateurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('allumer')
                ->label('Allumer')
                ->color('success')
                ->action(function () {
                    $this->record->update(['est_allumé' => true]);
                    $this->record->refresh();
                })
                ->visible(fn () => !$this->record->est_allumé),

            Actions\Action::make('eteindre')
                ->label('Éteindre')
                ->color('danger')
                ->action(function () {
                    $this->record->update(['est_allumé' => false]);
                    $this->record->refresh();
                })
                ->requiresConfirmation()
                ->modalHeading('Confirmation')
                ->modalDescription('Êtes-vous sûr de vouloir éteindre cet ordinateur ?')
                ->visible(fn () => $this->record->est_allumé),

            Actions\Action::make('mettre_a_jour')
                ->label('Mettre à jour')
                ->color('gray'),
        ];
    }
}
