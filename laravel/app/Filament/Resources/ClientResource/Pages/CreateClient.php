<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Service;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function getRedirectUrl(): string
    {
        $serviceIds = [1, 2];

        return route('filament.admin.resources.achats.create', [
            'client_id' => $this->record->id,
            'service_ids' => implode(',', $serviceIds),
        ]);
    }

}
