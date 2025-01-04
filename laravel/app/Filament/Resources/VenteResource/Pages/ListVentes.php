<?php

namespace App\Filament\Resources\VenteResource\Pages;

use App\Filament\Resources\VenteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVentes extends ListRecords
{
    protected static string $resource = VenteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
