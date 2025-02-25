<?php

namespace App\Filament\Resources\TailleResource\Pages;

use App\Filament\Resources\TailleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTailles extends ListRecords
{
    protected static string $resource = TailleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
