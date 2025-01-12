<?php

namespace App\Filament\Resources\OrdinateurResource\Pages;

use App\Filament\Resources\OrdinateurResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrdinateur extends ViewRecord
{
    protected static string $resource = OrdinateurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
