<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormuleResource\Pages;
use App\Filament\Resources\FormuleResource\RelationManagers;
use App\Filament\Resources\FormuleResource\Widgets\FormuleWidgets;
use App\Models\Formule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FormuleResource extends Resource
{
    protected static ?string $model = Formule::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'En vente';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('prix')
                            ->required()
                            ->numeric(),
                    ])
                    ->columns(2)
                    ->columnSpan(2),

                Forms\Components\Section::make('Durée')
                    ->description('Remplissez soit la durée en heures, soit la durée en jours. L’autre champ sera automatiquement désactivé.')
                    ->schema([
                        Forms\Components\TextInput::make('duree_en_heures')
                            ->label("Nombre d'heures")
                            ->numeric()
                            ->reactive()
                            ->default(null)
                            ->afterStateUpdated(fn ($state, $set) => $set('duree_en_jours', null))
                            ->required(fn ($get) => empty($get('duree_en_jours'))),
                        Forms\Components\TextInput::make('duree_en_jours')
                            ->label('Nombre de jours')
                            ->numeric()
                            ->default(null)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, $set) => $set('duree_en_heures', null))
                            ->required(fn ($get) => empty($get('duree_en_heures'))),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom'),
                Tables\Columns\TextColumn::make('duree_en_heures')
                    ->label('Durée (heures)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duree_en_jours')
                    ->label('Durée (jours)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prix')
                    ->label('Prix (€)')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ])
            ->paginated(false);
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
