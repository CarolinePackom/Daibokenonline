<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Récupère les 6 derniers mois avec le nombre de clients créés
        $monthlyData = DB::table('clients')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Transforme en tableau associatif [ '2025-01' => 12, '2025-02' => 15, ... ]
        $countsByMonth = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $countsByMonth[$month] = 0;
        }

        foreach ($monthlyData as $row) {
            $countsByMonth[$row->month] = $row->count;
        }

        $values = $countsByMonth->values()->toArray();
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        $currentCount = $countsByMonth[$currentMonth] ?? 0;
        $previousCount = $countsByMonth[$previousMonth] ?? 0;

        $percentageChange = $previousCount > 0
            ? round((($currentCount - $previousCount) / $previousCount) * 100)
            : null;

        $description = match (true) {
            is_null($percentageChange) => 'Pas de données le mois précédent',
            $percentageChange > 0 => "+$percentageChange% vs mois dernier",
            $percentageChange < 0 => "$percentageChange% vs mois dernier",
            default => 'Stable vs mois dernier',
        };

        $color = match (true) {
            is_null($percentageChange) => 'gray',
            $percentageChange > 0 => 'success',
            $percentageChange < 0 => 'danger',
            default => 'gray',
        };

        $icon = match (true) {
            is_null($percentageChange) => 'heroicon-m-minus',
            $percentageChange > 0 => 'heroicon-m-arrow-trending-up',
            $percentageChange < 0 => 'heroicon-m-arrow-trending-down',
            default => 'heroicon-m-minus',
        };

        return [
            Stat::make('Nouveaux clients ce mois-ci', number_format($currentCount, 0, ',', ' '))
                ->description($description)
                ->descriptionIcon($icon)
                ->chart($values)
                ->color($color),
        ];
    }
}
