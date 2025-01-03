<?php

namespace App\Filament\Resources\FormuleResource\Pages;

use App\Filament\Resources\FormuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFormule extends EditRecord
{
    protected static string $resource = FormuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
