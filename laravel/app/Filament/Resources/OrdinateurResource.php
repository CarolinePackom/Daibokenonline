<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdinateurResource\Pages;
use App\Filament\Resources\OrdinateurResource\RelationManagers;
use App\Models\Ordinateur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdinateurResource extends Resource
{
    protected static ?string $model = Ordinateur::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListOrdinateurs::route('/'),
            'create' => Pages\CreateOrdinateur::route('/create'),
            'view' => Pages\ViewOrdinateur::route('/{record}'),
            'edit' => Pages\EditOrdinateur::route('/{record}/edit'),
        ];
    }
}
