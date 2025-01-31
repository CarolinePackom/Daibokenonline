<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProduitResource\Pages;
use App\Filament\Resources\ProduitResource\RelationManagers;
use App\Models\Produit;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProduitResource extends Resource
{
    protected static ?string $model = Produit::class;

    protected static ?string $navigationGroup = 'En vente';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()
                    ->schema([
                        Section::make()
                            ->schema([
                                TextInput::make('nom')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('prix')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),
                                Textarea::make('description')
                                    ->autosize()
                                    ->columnSpan(2),
                            ])
                            ->columns(2),
                            Section::make('Image')
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('media')
                                        ->collection('produit-images')
                                        ->required()
                                        ->hiddenLabel(),
                                ])
                                ->collapsible(),
                            Section::make('Inventaire')
                                ->schema([
                                    TextInput::make('quantite_stock')
                                        ->label('Quantité en stock')
                                        ->required()
                                        ->numeric()
                                        ->minValue(0),
                                    TextInput::make('seuil_quantite_alerte')
                                        ->label("Seuil d'alerte")
                                        ->helperText('Quantité minimale avant d\'envoyer une alerte.')
                                        ->required()
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),
                                ])
                                ->columns(2),
                    ])
                    ->columnSpan(2),
                Group::make()
                    ->schema([
                        Section::make('Catégories')
                            ->schema([
                                Select::make('categories')
                                    ->relationship('categories', 'nom')
                                    ->hiddenLabel()
                                    ->options(
                                        \App\Models\Categorie::all()->pluck('nom', 'id')
                                    )
                                    ->multiple()
                                    ->preload()
                                    ->required(),
                            ]),
                        Section::make('Statut')
                            ->schema([
                                Toggle::make('en_vente')
                                    ->label('En vente')
                                    ->helperText('Il ne sera plus possible de vendre ce produit.')
                                    ->default(true),
                            ]),
                        Section::make('Options')
                            ->schema([
                                Checkbox::make('est_commandable_sur_le_logiciel')
                                    ->label('Commandable sur le logiciel')
                                    ->helperText('Ce produit peut être acheté depuis le logiciel sur les ordinateurs Daiboken.')
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('media')
                    ->collection('produit-images')
                    ->label('Image'),
                TextColumn::make('nom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('prix')
                    ->label('Prix (€)')
                    ->sortable(),
                TextColumn::make('quantite_stock')
                    ->label('Stock restant')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('en_vente')
                    ->label('En vente')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListProduits::route('/'),
            'create' => Pages\CreateProduit::route('/create'),
            'edit' => Pages\EditProduit::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Produit::query()->where('en_vente', true)->count();
    }
}
