<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormuleResource\Pages;
use App\Filament\Resources\FormuleResource\RelationManagers;
use App\Filament\Widgets\FormuleWidgets;
use App\Models\Formule;
use App\Models\Tarif;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class FormuleResource extends Resource
{
    protected static ?string $model = Formule::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'En vente';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('duree_en_heures')
                    ->label('Durée en heures')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('duree_en_jours')
                    ->label('Durée en jours')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('prix')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duree_en_heures')
                    ->label('Durée en heures')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duree_en_jours')
                    ->label('Durée en jours')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prix')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormules::route('/'),
            'create' => Pages\CreateFormule::route('/create'),
            'edit' => Pages\EditFormule::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Formule::query()->count();
    }

    public static function getWidgets(): array
    {
        return [
            FormuleWidgets::class,
        ];
    }
}
