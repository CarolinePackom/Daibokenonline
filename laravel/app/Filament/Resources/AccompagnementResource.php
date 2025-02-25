<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccompagnementResource\Pages;
use App\Filament\Resources\AccompagnementResource\RelationManagers;
use App\Models\Menus\Accompagnement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccompagnementResource extends Resource
{
    protected static ?string $model = Accompagnement::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationGroup = 'Gestion des menus';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('prix_supplementaire')
                            ->label('Prix Supplémentaire')
                            ->required()
                            ->numeric(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom'),
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
        return (string) Accompagnement::query()->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccompagnements::route('/'),
            'create' => Pages\CreateAccompagnement::route('/create'),
            'edit' => Pages\EditAccompagnement::route('/{record}'),
        ];
    }
}
