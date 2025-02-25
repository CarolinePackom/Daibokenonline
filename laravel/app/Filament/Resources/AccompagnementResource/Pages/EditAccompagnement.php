<?php

namespace App\Filament\Resources\AccompagnementResource\Pages;

use App\Filament\Resources\AccompagnementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccompagnement extends EditRecord
{
    protected static string $resource = AccompagnementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
