<?php

namespace App\Filament\Resources\SauceResource\Pages;

use App\Filament\Resources\SauceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSauce extends EditRecord
{
    protected static string $resource = SauceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
