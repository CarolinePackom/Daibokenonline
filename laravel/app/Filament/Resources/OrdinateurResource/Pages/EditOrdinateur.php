<?php

namespace App\Filament\Resources\OrdinateurResource\Pages;

use App\Filament\Resources\OrdinateurResource;
use App\Models\Ordinateurs\Ordinateur;
use App\Services\WindowsService;
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
                ->action(function (Ordinateur $record) {
                    $record->allumer();
                    sleep(30);
                    $record->refresh();
                })
                ->visible(fn (Ordinateur $record) => !$record->fresh()->est_allumé),

            Actions\Action::make('eteindre')
                ->label('Éteindre')
                ->color('danger')
                ->action(function (Ordinateur $record) {
                    $record->eteindre();
                    sleep(30);
                    $record->refresh();
                })
                ->requiresConfirmation()
                ->modalHeading('Confirmation')
                ->modalDescription('Êtes-vous sûr de vouloir éteindre cet ordinateur ?')
                ->visible(fn (Ordinateur $record) => $record->fresh()->est_allumé),

            Actions\Action::make('mettre_a_jour')
                ->label('Mettre à jour')
                ->color('gray')
                ->action(function (Ordinateur $record) {
                    $record->mettreAJour();
                })
                ->visible(fn (Ordinateur $record) => $record->fresh()->est_allumé),
        ];
    }
}
