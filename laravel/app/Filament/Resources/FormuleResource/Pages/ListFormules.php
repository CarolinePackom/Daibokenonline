<?php

namespace App\Filament\Resources\FormuleResource\Pages;

use App\Filament\Resources\FormuleResource;
use App\Filament\Resources\FormuleResource\Widgets\FormuleWidgets;
use App\Models\Tarif;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListFormules extends ListRecords
{
    protected static string $resource = FormuleResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            FormuleWidgets::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('modifierTarifs')
                ->label('Modifier les tarifs globaux')
                ->icon('heroicon-o-pencil')
                ->color('gray')
                ->form([
                    Grid::make(2)->schema([
                        TextInput::make('prix_une_heure')
                            ->label('Prix par heure (€)')
                            ->numeric()
                            ->default(fn () => Tarif::firstOrCreate()->prix_une_heure)
                            ->required(),
                        TextInput::make('prix_un_jour')
                            ->label('Prix par jour (€)')
                            ->numeric()
                            ->default(fn () => Tarif::firstOrCreate()->prix_un_jour)
                            ->required(),
                ]),
                ])
                ->action(function (array $data) {
                    $tarif = Tarif::first();
                    $tarif->update([
                        'prix_une_heure' => $data['prix_une_heure'],
                        'prix_un_jour' => $data['prix_un_jour'],
                    ]);
                    return redirect()->route('filament.admin.resources.formules.index');
                }),
            Actions\CreateAction::make(),
        ];
    }
}
