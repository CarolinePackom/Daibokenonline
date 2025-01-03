<?php

namespace App\Filament\Widgets;

use App\Models\Tarif;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FormuleWidgets extends BaseWidget
{
    protected $listeners = ['refreshTarifWidgets' => 'refreshData'];

    public function refreshData()
    {
        // Rafraîchit les données du widget
        $this->mount();
    }

    protected function getStats(): array
    {
        $tarif = Tarif::firstOrCreate();

        return [
            Stat::make('Prix par heure', $tarif->prix_une_heure)
                ->description('Tarif global pour une heure')
                ->descriptionIcon('heroicon-o-currency-euro'),

            Stat::make('Prix par jour', $tarif->prix_un_jour)
                ->description('Tarif global pour une journée')
                ->descriptionIcon('heroicon-o-currency-euro')
        ];
    }
}
