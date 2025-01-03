<?php

namespace App\Filament\Resources\AchatResource\Pages;

use App\Filament\Resources\AchatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAchat extends EditRecord
{
    protected static string $resource = AchatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
