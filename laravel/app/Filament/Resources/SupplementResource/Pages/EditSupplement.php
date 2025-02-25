<?php

namespace App\Filament\Resources\SupplementResource\Pages;

use App\Filament\Resources\SupplementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupplement extends EditRecord
{
    protected static string $resource = SupplementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
