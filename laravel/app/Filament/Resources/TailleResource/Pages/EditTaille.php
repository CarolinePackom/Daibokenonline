<?php

namespace App\Filament\Resources\TailleResource\Pages;

use App\Filament\Resources\TailleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTaille extends EditRecord
{
    protected static string $resource = TailleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
