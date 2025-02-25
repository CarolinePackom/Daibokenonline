<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplementResource\Pages;
use App\Filament\Resources\SupplementResource\RelationManagers;
use App\Models\Menus\Supplement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplementResource extends Resource
{
    protected static ?string $model = Supplement::class;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?int $navigationSort = 11;

    protected static ?string $label = 'Suppléments';
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
        return (string) Supplement::query()->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupplements::route('/'),
            'create' => Pages\CreateSupplement::route('/create'),
            'edit' => Pages\EditSupplement::route('/{record}'),
        ];
    }
}
