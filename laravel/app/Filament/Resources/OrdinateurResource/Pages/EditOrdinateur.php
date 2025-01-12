<?php

namespace App\Filament\Resources\OrdinateurResource\Pages;

use App\Filament\Resources\OrdinateurResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrdinateur extends EditRecord
{
    protected static string $resource = OrdinateurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
