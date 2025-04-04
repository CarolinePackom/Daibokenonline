<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClientsChart extends ChartWidget
{
    protected static ?string $heading = 'Total clients';

    protected function getData(): array
    {
        $data = DB::table('clients')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $data->map(function ($item) {
            return ucfirst(Carbon::createFromFormat('Y-m', $item->month)->translatedFormat('M'));
        })->toArray();

        $values = $data->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Clients',
                    'data' => $values,
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                        'beginAtZero' => true,
                    ],
                ],
            ],
        ];
    }
}
