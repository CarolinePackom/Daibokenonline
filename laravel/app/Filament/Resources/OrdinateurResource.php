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
use Illuminate\Validation\Rule;

class OrdinateurResource extends Resource
{
    protected static ?string $model = Ordinateur::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationGroup = 'Gestion';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('adresse_ip')
                            ->label('Adresse IP')
                            ->required()
                            ->unique(),
                        Forms\Components\TextInput::make('adresse_mac')
                            ->label('Adresse MAC')
                            ->required(),
                    ])
            ->columnSpan(1),
                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\Toggle::make('en_maintenance')
                            ->label('En maintenance')
                            ->helperText("L'ordinateur ne sera pas utilisable pendant la maintenance.")
                            ->default(false),
                    ])
                ->columnSpan(1),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom'),
                Tables\Columns\ToggleColumn::make('est_allumé')
                    ->label('Allumé')
                    ->getStateUsing(fn (Ordinateur $record) => $record->estEnLigne())
                    ->onColor('success')
                    ->offColor('danger')
                    ->afterStateUpdated(function ($state, Ordinateur $record) {
                        if ($state) {
                            $record->allumer();
                        } else {
                            $record->eteindre();
                        }
                    }),
                Tables\Columns\TextColumn::make('client.nom_complet')
                    ->label('Client actuel')
                    ->getStateUsing(function ($record) {
                        $historique = $record->historiqueClients()
                            ->whereNull('fin_utilisation')
                            ->with('client')
                            ->first();
                        if ($historique?->client) {
                            return $historique->client->prenom . ' ' . $historique->client->nom;
                        }
                        return 'Aucun';
                    }),
                Tables\Columns\IconColumn::make('en_maintenance')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_update')
                    ->label('Dernière mise à jour')
                    ->since()
                    ->dateTimeTooltip()
            ])
            ->actions([
                Tables\Actions\Action::make('mettre_a_jour')
                    ->label('Mettre à jour')
                    ->button()
                    ->action(function (Ordinateur $record) {
                        $record->mettreAJour();
                    })
                    ->color('gray'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('tout_allumer')
                    ->label('Tout allumer')
                    ->color('success')
                    ->action(function () {
                        Ordinateur::all()->each(function ($ordinateur) {
                            $ordinateur->allumer();
                        });
                    }),

                Tables\Actions\Action::make('tout_eteindre')
                    ->label('Tout éteindre')
                    ->color('danger')
                    ->action(function () {
                        Ordinateur::all()->each(function ($ordinateur) {
                            if ($ordinateur->estEnLigne()) { // Vérifier si l'ordinateur est allumé
                                $ordinateur->eteindre();
                            }
                        });
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Confirmation')
                    ->modalDescription('Êtes-vous sûr de vouloir éteindre tous les ordinateurs ?'),

                Tables\Actions\Action::make('tout_mettre_a_jour')
                    ->label('Tout mettre à jour')
                    ->action(function () {
                        Ordinateur::all()->each(function ($ordinateur) {
                            $ordinateur->mettreAJour();
                        });
                    })
                    ->color('gray'),
            ])
            ->paginated(false)
            ->poll('2s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HistoriqueOrdinateursRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Ordinateur::query()->where('est_allumé', true)->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdinateurs::route('/'),
            'edit' => Pages\EditOrdinateur::route('/{record}/edit'),
        ];
    }
}
