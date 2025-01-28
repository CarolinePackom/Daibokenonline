<?php

namespace App\Filament\Resources\CategorieResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProduitsRelationManager extends RelationManager
{
    protected static string $relationship = 'produits';

    protected static ?string $recordTitleAttribute = 'nom';

    public function form(Form $form): Form
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

    public function table(Table $table): Table
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
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
