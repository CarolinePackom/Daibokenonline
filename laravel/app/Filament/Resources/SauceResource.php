<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SauceResource\Pages;
use App\Filament\Resources\SauceResource\RelationManagers;
use App\Models\Menus\Sauce;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SauceResource extends Resource
{
    protected static ?string $model = Sauce::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationGroup = 'Gestion des menus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\TextInput::make('nom')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('prix_supplementaire')
                                    ->label('Prix Supplémentaire (€)')
                                    ->required()
                                    ->numeric(),
                            ])
                            ->columns(2),

                        Forms\Components\TextInput::make('description')
                            ->required()
                            ->maxLength(255),
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom'),
                Tables\Columns\TextColumn::make('description'),
                Tables\Columns\TextColumn::make('prix_supplementaire')
                    ->label('Prix Supplémentaire (€)')
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

    public static function getNavigationBadge(): ?string
    {
        return (string) Sauce::query()->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSauces::route('/'),
            'create' => Pages\CreateSauce::route('/create'),
            'edit' => Pages\EditSauce::route('/{record}'),
        ];
    }
}
