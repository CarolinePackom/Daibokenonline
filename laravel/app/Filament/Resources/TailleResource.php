<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TailleResource\Pages;
use App\Filament\Resources\TailleResource\RelationManagers;
use App\Models\Menus\Taille;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TailleResource extends Resource
{
    protected static ?string $model = Taille::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-pointing-in';

    protected static ?int $navigationSort = 8;

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
                        Forms\Components\TextInput::make('prix')
                            ->label('Prix')
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

    public static function getNavigationBadge(): ?string
    {
        return (string) Taille::query()->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTailles::route('/'),
            'create' => Pages\CreateTaille::route('/create'),
            'edit' => Pages\EditTaille::route('/{record}'),
        ];
    }
}
