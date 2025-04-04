<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdinateurResource\Pages;
use App\Filament\Resources\OrdinateurResource\RelationManagers;
use App\Models\Ordinateurs\Ordinateur;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ->getStateUsing(fn (Ordinateur $record) => $record->est_allumé)
                    ->onColor('success')
                    ->offColor('danger')
                    ->afterStateUpdated(function ($state, Ordinateur $record) {
                        if ($state) {
                            $record->allumer();
                        } else {
                            $record->eteindre();
                        }
                        sleep(30);
                        $record->refresh();
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
                    ->dateTimeTooltip(),

            ])
            ->actions([
                Tables\Actions\Action::make('daiboken')
        ->label('Daiboken')
        ->icon('heroicon-o-user')
        ->action(function (Ordinateur $record, array $data) {
            try {
                if ($data['operation'] === 'create') {
                    $record->creerUtilisateur("Daiboken");
                    \Filament\Notifications\Notification::make()
                        ->title('Utilisateur créé')
                        ->body("L'utilisateur 'Daiboken' a bien été créé sur {$record->nom}.")
                        ->success()
                        ->send();
                } else {
                    $record->supprimerUtilisateur("Daiboken");
                    \Filament\Notifications\Notification::make()
                        ->title('Utilisateur supprimé')
                        ->body("L'utilisateur 'Daiboken' a bien été supprimé de {$record->nom}.")
                        ->success()
                        ->send();
                }
            } catch (\Exception $e) {
                \Filament\Notifications\Notification::make()
                    ->title('Erreur')
                    ->body("Une erreur est survenue : " . $e->getMessage())
                    ->danger()
                    ->send();
            }
            sleep(30);
            $record->refresh();
        })
        ->form([
            Forms\Components\Select::make('operation')
                ->label('Opération')
                ->options([
                    'create' => 'Créer Daiboken',
                    'delete' => 'Supprimer Daiboken',
                ])
                ->required(),
        ])
        ->visible(fn (Ordinateur $record) => $record->est_allumé && is_null($record->clientActuel()->first())),
                /*
                Tables\Actions\Action::make('mettre_a_jour')
                    ->label('Mettre à jour')
                    ->button()
                    ->action(function (Ordinateur $record) {
                        $record->mettreAJour();
                    })
                    ->color('gray')
                    ->visible(fn (Ordinateur $record) => $record->est_allumé),
                */
            ])
            ->headerActions([
                Tables\Actions\Action::make('tout_allumer')
                    ->label('Tout allumer')
                    ->color('success')
                    ->action(function () {
                        Ordinateur::all()->each(function ($ordinateur) {
                            try {
                                $ordinateur->allumer();
                            } catch (\Exception $e) {
                                \Log::error("Erreur lors de l'allumage de l'ordinateur {$ordinateur->adresse_ip}: " . $e->getMessage());
                            }
                        });
                        sleep(30);
                    })
                    ->visible(fn () => Ordinateur::where('est_allumé', false)->count() > 0),

                Tables\Actions\Action::make('tout_eteindre')
                    ->label('Tout éteindre')
                    ->color('danger')
                    ->action(function () {
                        Ordinateur::all()->each(function ($ordinateur) {
                            if ($ordinateur->est_allumé) {
                                try {
                                    $ordinateur->eteindre();
                                } catch (\Exception $e) {
                                    \Log::error("Erreur lors de l'extinction de l'ordinateur {$ordinateur->adresse_ip}: " . $e->getMessage());
                                }
                            }
                        });
                        sleep(30);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Confirmation')
                    ->modalDescription('Êtes-vous sûr de vouloir éteindre tous les ordinateurs ?')
                    ->visible(fn () => Ordinateur::where('est_allumé', true)->count() > 0),

                /*
                Tables\Actions\Action::make('tout_mettre_a_jour')
                    ->label('Tout mettre à jour')
                    ->action(function () {
                        Ordinateur::all()->each(function ($ordinateur) {
                            try {
                                $ordinateur->mettreAJour();
                            } catch (\Exception $e) {
                                \Log::error("Erreur lors de la mise à jour de l'ordinateur {$ordinateur->adresse_ip}: " . $e->getMessage());
                            }
                        });
                    })
                    ->color('gray')
                    ->visible(fn () => Ordinateur::where('est_allumé', true)->count() > 0),
                */

                Tables\Actions\Action::make('changer_mdp_global')
                    ->label('Mot de passe Admin')
                    ->icon('heroicon-o-key')
                    ->form([
                        Forms\Components\TextInput::make('mot_de_passe')
                            ->label('Nouveau mot de passe SSH')
                            ->password()
                            ->revealable()
                            ->default(fn () => \App\Models\Identifiant::getGlobal()?->mot_de_passe)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        // Vérifie que TOUS les ordis sont allumés
                        $total = \App\Models\Ordinateurs\Ordinateur::count();
                        $allumes = \App\Models\Ordinateurs\Ordinateur::where('est_allumé', true)->count();

                        if ($total !== $allumes) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erreur')
                                ->body("Tous les ordinateurs doivent être allumés pour changer le mot de passe.")
                                ->danger()
                                ->send();
                            return;
                        }

                        $identifiant = \App\Models\Identifiant::first() ?? new \App\Models\Identifiant();
                        $ancienMdp = $identifiant->mot_de_passe;
                        $identifiant->mot_de_passe = $data['mot_de_passe'];
                        $identifiant->save();

                        $totalMachines = 0;
                        $machinesOk = 0;

                        \App\Models\Ordinateurs\Ordinateur::where('est_allumé', true)->each(function ($ordinateur) use ($ancienMdp, $data, &$totalMachines, &$machinesOk) {
                            $totalMachines++;

                            try {
                                $ordinateur->changerMotDePasseLocal($ancienMdp, $data['mot_de_passe']);
                                $machinesOk++;
                            } catch (\Throwable $e) {
                                \Log::warning("Erreur changement mot de passe sur {$ordinateur->adresse_ip} : " . $e->getMessage());
                            }
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Mot de passe mis à jour')
                            ->body("Mise à jour effectuée sur {$machinesOk}/{$totalMachines} ordinateurs.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => \App\Models\Ordinateurs\Ordinateur::where('est_allumé', false)->count() === 0)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Modifier le mot de passe')
                    ->modalSubmitActionLabel('Enregistrer')
                    ->modalDescription("Le mot de passe est utilisé pour se connecter à tous les ordinateurs."),
            ])
            ->paginated(false)
            ->poll('5s');
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
