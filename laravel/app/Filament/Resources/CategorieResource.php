<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategorieResource\Pages;
use App\Filament\Resources\CategorieResource\RelationManagers;
use App\Models\Categorie;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Guava\FilamentIconPicker\Forms\IconPicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategorieResource extends Resource
{
    protected static ?string $model = Categorie::class;

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $label = 'Catégories des produits';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->maxLength(255),
                IconPicker::make('icone')
                    ->label('Icône')
                    ->required()
                    ->columns(3)
                    ->sets(['fontawesome-solid']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('icone')
                    ->label('')
                    ->icon(fn ($state) => $state)
                    ->grow(false),
                Tables\Columns\TextColumn::make('nom')
                ->label(''),
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
            RelationManagers\ProduitsRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Categorie::query()->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategorie::route('/create'),
            'edit' => Pages\EditCategorie::route('/{record}'),
        ];
    }
}
