<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProduitResource\Pages;
use App\Filament\Resources\ProduitResource\RelationManagers;
use App\Models\Produit;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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
                            ]),
                            Section::make('Images')
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('media')
                                        ->collection('produit-images')
                                        ->hiddenLabel(),
                                ])
                    ]),
                Group::make()
                    ->schema([
                        Section::make('Statut')
                            ->schema([
                                Toggle::make('en_vente')
                                    ->label('En vente')
                                    ->helperText('Il ne sera plus possible de vendre ce produit.')
                                    ->default(true),
                            ])
                    ])
                    ->columnSpan(['lg' => 1]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('media')
                    ->collection('produit-images')
                    ->label('Image'),
                TextColumn::make('nom')->sortable()->searchable(),
                TextColumn::make('prix')->sortable(),
                IconColumn::make('en_vente')
                    ->label('En vente')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
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
