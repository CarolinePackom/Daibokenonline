<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-dollar';

    protected static ?int $navigationSort = 4;

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
                    ->sortable()
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Service::query()->count();
    }
}
