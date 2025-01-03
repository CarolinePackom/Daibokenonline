<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Archiver')
                ->icon('heroicon-o-archive-box')
                ->color('danger')
                ->action(function () {
                    $randomPrefix = rand(1000, 9999);
                    $archivedEmail = "{$randomPrefix}_{$this->record->email}";

                    $this->record->update([
                        'archived_at' => now(),
                        'est_present' => false,
                        'id_nfc' => null,
                        'email' => $archivedEmail,
                    ]);

                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->requiresConfirmation()
                ->tooltip('Archiver ce client'),
        ];
    }
}
