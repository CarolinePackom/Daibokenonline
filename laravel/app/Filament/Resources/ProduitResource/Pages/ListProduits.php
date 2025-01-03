<?php

namespace App\Filament\Resources\ProduitResource\Pages;

use App\Filament\Resources\ProduitResource;
use App\Filament\Widgets\ProduitWidgets;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProduits extends ListRecords
{
    protected static string $resource = ProduitResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ProduitWidgets::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
