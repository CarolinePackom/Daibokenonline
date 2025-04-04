<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use App\Models\Ordinateurs\HistoriqueOrdinateur;
use App\Models\Ordinateurs\Ordinateur;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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
                                Forms\Components\TextInput::make('prenom')
                                    ->label('Prénom')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('nom')
                                    ->label('Nom')
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

                Tables\Columns\ToggleColumn::make('est_present')
                    ->label('Présent')
                    ->sortable()
                    ->onColor('success')
                    ->offColor('danger')
                    ->updateStateUsing(function (Model $record, bool $state) {
                        $record->update(['est_present' => $state]);

                        if (!$state) {
                            $record->deconnecterOrdinateur();
                        }

                        return $record->refresh();
                    }),

                Tables\Columns\SelectColumn::make('ordinateur_id')
                    ->label('Ordinateur')
                    ->options(function (Client $record) {
                        $ordinateursUtilises = HistoriqueOrdinateur::whereNull('fin_utilisation')
                            ->where('client_id', '!=', $record->id)
                            ->pluck('ordinateur_id')
                            ->toArray();

                        // Récupérer l'historique d'utilisation en cours (s'il existe)
                        $historique = $record->historiqueOrdinateurs()->whereNull('fin_utilisation')->first();
                        $ordinateurActuel = $historique ? $historique->ordinateur_id : null;

                        // Récupération de la liste des ordinateurs disponibles
                        $ordinateurs = Ordinateur::where('en_maintenance', false)
                            ->where(function ($query) use ($ordinateursUtilises, $ordinateurActuel) {
                                $query->whereNotIn('id', $ordinateursUtilises)
                                      ->where(function ($q) use ($ordinateurActuel) {
                                          $q->where('est_allumé', true)
                                            ->orWhere('id', $ordinateurActuel);
                                      });
                            })
                            ->pluck('nom', 'id');

                        // Si le client utilise un ordinateur, ajoute le temps écoulé à son nom
                        if ($ordinateurActuel && $historique) {
                            $diff = $historique->created_at->diff(now());

                            // Construire la chaîne d'affichage avec heures et minutes
                            $timeUsed = '';
                            if ($diff->h > 0) {
                                $timeUsed .= $diff->h . 'h ';
                            }
                            if ($diff->i > 0 || $timeUsed === '') { // Toujours afficher les minutes
                                $timeUsed .= $diff->i . 'min';
                            }

                            // Modifier la liste des ordinateurs
                            $ordinateurs = $ordinateurs->map(function ($name, $id) use ($ordinateurActuel, $timeUsed) {
                                if ($id == $ordinateurActuel) {
                                    return $name . ' depuis ' . $timeUsed;
                                }
                                return $name;
                            });
                        }

                        return [null => 'Aucun ordinateur'] + $ordinateurs->toArray();
                    })
                    ->updateStateUsing(function (Client $record, $state) {
                        if (Cache::has("selection_lock_{$record->id}")) {
                            return;
                        }

                        if ($state) {
                            $record->connecterOrdinateur($state);
                        } else {
                            $record->deconnecterOrdinateur();
                        }

                        // Placer un verrou dans le cache pendant 30 secondes
                        Cache::put("selection_lock_{$record->id}", true, 30);
                        $record->refresh();
                    })
                    ->default(null)
                    ->selectablePlaceholder(false)
                    ->disabled(fn (?Client $record) =>
                        !$record->est_present || Cache::has("selection_lock_{$record->id}")
                    )
                    ->getStateUsing(function (Client $record) {
                        $historique = $record->historiqueOrdinateurs()
                            ->whereNull('fin_utilisation')
                            ->first();

                        return $historique ? $historique->ordinateur_id : null;
                    }),

                Tables\Columns\TextColumn::make('solde_credit')
                    ->label('Solde crédit')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('vendre')
                    ->url(function (Model $record) {
                        session()->put('previous_previous_url', url()->previous());

                        return route('filament.admin.resources.ventes.create', ['client_id' => $record->id]);
                    })
                    ->button()
                    ->hiddenLabel()
                    ->icon('heroicon-o-shopping-bag'),
            ])
            ->poll('5s')
            ->paginated(false)
            ->deferLoading()
            ->defaultSort('est_present', 'desc');
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
