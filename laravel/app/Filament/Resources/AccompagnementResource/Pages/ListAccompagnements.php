<?php

namespace App\Filament\Resources\AccompagnementResource\Pages;

use App\Filament\Resources\AccompagnementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccompagnements extends ListRecords
{
    protected static string $resource = AccompagnementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
