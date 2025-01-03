<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('Tous')->query(fn ($query) => $query->whereNull('archived_at')),
            'présents' => Tab::make()->query(fn ($query) => $query->where('est_present', true)->whereNull('archived_at')),
            'absents' => Tab::make()->query(fn ($query) => $query->where('est_present', false)->whereNull('archived_at')),
            'archivés' => Tab::make()->query(fn ($query) => $query->whereNotNull('archived_at')),
        ];
    }
}
