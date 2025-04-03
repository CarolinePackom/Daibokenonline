<?php

namespace App\Filament\Resources\ProduitResource\Pages;

use App\Filament\Resources\ProduitResource;
use App\Models\Produits\Produit;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListProduits extends ListRecords
{
    protected static string $resource = ProduitResource::class;

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
                ->query(fn () => Produit::query())
                ->badge(fn () => Produit::count()),
            'En vente' => Tab::make()
                ->query(fn () => Produit::where('en_vente', true))
                ->badge(fn () => Produit::where('en_vente', true)->count())
                ->badgeColor('success'),
            'Non commercialisé' => Tab::make()
                ->query(fn () => Produit::where('en_vente', false))
                ->badge(fn () => Produit::where('en_vente', false)->count())
                ->badgeColor('danger'),
        ];
    }
}
