<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('nom')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('prenom')
                                    ->label('Prénom')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->required()
                                    ->email()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('telephone')
                                    ->label('Téléphone')
                                    ->tel(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Carte NFC')
                            ->schema([
                                Forms\Components\View::make('nfc-sse-script')
                                    ->view('filament.custom-scripts.nfc-sse'),

                                Forms\Components\TextInput::make('id_nfc')
                                    ->label('ID NFC')
                                    ->unique(ignoreRecord: true)
                                    ->required()
                                    ->disabled()
                                    ->default(request('id_nfc') ?? null)
                                    ->dehydrated(fn ($state) => !is_null($state))
                                    ->reactive()
                                    ->extraAttributes(['id' => 'id_nfc']),
                            ]),
                        Forms\Components\Section::make('Présence')
                            ->schema([
                                Toggle::make('est_present')
                                    ->label('Client présent dans la salle')
                                    ->default(false),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom_complet')
                    ->label('Nom')
                    ->getStateUsing(fn (Client $record) => ucfirst($record->prenom) . ' ' . ucfirst($record->nom))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('est_present')
                    ->label('Présent')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger'),
                Tables\Columns\TextColumn::make('solde_credit')
                    ->label('Solde crédit')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_nfc')
                    ->label('ID NFC')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Client::query()->where('est_present', true)->count();
    }
}
