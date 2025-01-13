<?php

namespace App\Filament\Resources\OrdinateurResource\RelationManagers;

use Carbon\Carbon;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class HistoriqueOrdinateursRelationManager extends RelationManager
{
    protected static string $relationship = 'historiqueClients';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.full_name')
                    ->label('Client')
                    ->getStateUsing(function ($record) {
                        return ucfirst($record->client->prenom) . ' ' . ucfirst($record->client->nom);
                    })
                    ->searchable(['client.prenom', 'client.nom']),
                Tables\Columns\TextColumn::make('debut_utilisation')
                    ->label('Début')
                    ->date('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fin_utilisation')
                    ->label('Fin')
                    ->getStateUsing(function ($record) {
                        return $record->fin_utilisation
                            ? Carbon::parse($record->fin_utilisation)->format('d/m/Y H:i')
                            : 'En cours';
                    })
                    ->sortable(),
            ])
            ->defaultSort('fin_utilisation', 'desc')
            ->modifyQueryUsing(function ($query) {
                $query->orderByRaw('ISNULL(fin_utilisation) DESC')
                      ->orderBy('fin_utilisation', 'desc');
            });

    }
}
