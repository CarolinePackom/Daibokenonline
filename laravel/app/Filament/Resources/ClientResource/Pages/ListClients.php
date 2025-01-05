<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
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
            null => Tab::make('Tous')
                ->query(fn () => Client::whereNull('archived_at'))
                ->badge(fn () => Client::whereNull('archived_at')->count()),
            'présents' => Tab::make()
                ->query(fn () => Client::where('est_present', true)->whereNull('archived_at'))
                ->badge(fn () => Client::where('est_present', true)->whereNull('archived_at')->count())
                ->badgeColor('success'),
            'absents' => Tab::make()
                ->query(fn () => Client::where('est_present', false)->whereNull('archived_at'))
                ->badge(fn () => Client::where('est_present', false)->whereNull('archived_at')->count())
                ->badgeColor('danger'),
            'archivés' => Tab::make()
                ->query(fn () => Client::whereNotNull('archived_at'))
                ->badge(fn () => Client::whereNotNull('archived_at')->count())
                ->badgeColor('gray'),
        ];
    }
}
