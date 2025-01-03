<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProduitResource\Pages\ListProduits;
use App\Models\Produit;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProduitWidgets extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Tous les produits', Produit::query()->count()),
            Stat::make('Produits en vente', Produit::query()->where('en_vente', true)->count()),
        ];
    }
}
