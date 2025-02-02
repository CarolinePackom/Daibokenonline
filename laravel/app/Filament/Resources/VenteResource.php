<?php

namespace App\Filament\Resources;

use App\Enums\StatutEnum;
use App\Filament\Resources\VenteResource\Pages;
use App\Models\Credit;
use App\Models\Tarif;
use App\Models\Vente;
use App\Models\Client;
use App\Models\Formule;
use App\Models\Produit;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Button;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

class VenteResource extends Resource
{
    protected static ?string $model = Vente::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // ...
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn (Vente $record) => number_format($record->total, 2, ',', ' ') . ' €')
                    ->sortable(),
                // autres colonnes...
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
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
            'index'  => Pages\ListVentes::route('/'),
            'create' => Pages\CreateVente::route('/create'),
        ];
    }

    public static function getCreationFormSchema(?int $clientId = null): array
    {
        $resolvedClientId = request('client_id') ?? $clientId;

        return [
            Forms\Components\Group::make()
                ->schema([
                    // Sélection du client
                    Section::make('')
                        ->schema([
                            Forms\Components\Select::make('client_id')
                                ->label('Client')
                                ->options(fn () => Client::whereNull('archived_at')
                                    ->get()
                                    ->mapWithKeys(fn ($client) => [
                                        $client->id => ucfirst(strtolower($client->prenom))
                                            . ' ' . ucfirst(strtolower($client->nom)),
                                    ]))
                                ->searchable()
                                ->prefixIcon('heroicon-m-user')
                                ->default($resolvedClientId)
                                ->disabled($resolvedClientId !== null)
                                ->required()
                                ->placeholder('Sélectionnez un client'),
                        ]),
                    // Répétiteur de produits
                    Repeater::make('produits')
                        ->schema([
                            Forms\Components\Select::make('produit_id')
                                ->label('Nom')
                                ->options(
                                    Produit::query()
                                        ->where('en_vente', true)
                                        ->where('quantite_stock', '>', 0)
                                        ->pluck('nom', 'id')
                                )
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $produit = Produit::find($state);
                                    if ($produit) {
                                        $set('quantite_max', $produit->quantite_stock);
                                        $quantite = min($get('quantite') ?? 1, $produit->quantite_stock);
                                        $set('quantite', $quantite);
                                        $set('prix', $produit->prix * $quantite);
                                    } else {
                                        $set('quantite_max', null);
                                        $set('quantite', 1);
                                        $set('prix', 0);
                                    }
                                    self::updateTotal($set, $get);
                                })
                                ->distinct()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->columnSpan(2)
                                ->placeholder('Sélectionnez un produit')
                                ->nullable()
                                ->searchable(),

                            Hidden::make('quantite_max')
                                ->reactive(),

                            Forms\Components\TextInput::make('quantite')
                                ->label('Quantité')
                                ->numeric()
                                ->default(1)
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $quantiteMax = $get('quantite_max') ?? 1;
                                    $quantite = min($state, $quantiteMax);
                                    if ($state > $quantiteMax) {
                                        $set('quantite', $quantite);
                                    }
                                    if ($state < 0) {
                                        $set('quantite', 0);
                                    }
                                    if ($produit = Produit::find($get('produit_id'))) {
                                        $set('prix', $produit->prix * $quantite);
                                    }
                                    self::updateTotal($set, $get);
                                })
                                ->live()
                                ->disabled(fn (Get $get) => $get('quantite_max') === null)
                                ->reactive()
                                ->maxValue(fn (Get $get) => $get('quantite_max') ?? 1)
                                ->minValue(0)
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('prix')
                                ->label('Prix')
                                ->disabled()
                                ->dehydrated()
                                ->numeric()
                                ->default(0)
                                ->columnSpan(1),
                        ])
                        ->defaultItems(1)
                        ->hiddenLabel()
                        ->addActionLabel('Ajouter un produit')
                        ->reorderable(false)
                        ->itemLabel("Produit")
                        ->columns(4),
                ])
                ->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    // Choix du statut
                    Section::make('Statut')
                        ->schema([
                            ToggleButtons::make('statut')
                                ->hiddenLabel()
                                ->options(collect(StatutEnum::cases())
                                    ->mapWithKeys(fn (StatutEnum $statut) => [
                                        $statut->value => $statut->getLabel(),
                                    ])
                                    ->toArray())
                                ->colors(collect(StatutEnum::cases())
                                    ->mapWithKeys(fn (StatutEnum $statut) => [
                                        $statut->value => $statut->getColor(),
                                    ])
                                    ->toArray())
                                ->icons(collect(StatutEnum::cases())
                                    ->mapWithKeys(fn (StatutEnum $statut) => [
                                        $statut->value => $statut->getIcon(),
                                    ])
                                    ->toArray())
                                ->required()
                                ->inline()
                                ->default(StatutEnum::Pret->value),
                        ]),
                    // Choix de la formule ou personnalisation
                    Forms\Components\Tabs::make()
                        ->tabs([
                            Forms\Components\Tabs\Tab::make('Formules')
                                ->schema([
                                    // Groupe contenant le toggle et un bouton de réinitialisation
                                    Forms\Components\Group::make()
                                        ->schema([
                                            ToggleButtons::make('formule_id')
                                                ->label('')
                                                ->options(Formule::pluck('nom', 'id'))
                                                ->nullable()
                                                ->gridDirection('row')
                                                ->disabled(fn (Get $get) => !empty($get('custom_duration')))
                                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                    if ($state !== null) {
                                                        $set('custom_duration', null);
                                                    }
                                                    // Si le toggle pour crédit est actif, recalculer la réduction
                                                    if ($get('utiliser_credit_pour_payer')) {
                                                        $client = Client::find($get('client_id'));
                                                        $soldeCredit = $client?->solde_credit ?? 0;
                                                        $totalSansCredits = self::getTotalSansCredits($get);
                                                        $discount = min($soldeCredit, $totalSansCredits);
                                                        $set('nombre_credits', -$discount);
                                                        $set('placeholder_credit', 'Réduction appliquée : -' . number_format($discount, 2, ',', ' ') . ' €');
                                                        $set('moyen_paiement', 'credit');
                                                    }
                                                    self::updateTotal($set, $get);
                                                })
                                                ->columns(2),
                                        ]),
                                ]),
                            Forms\Components\Tabs\Tab::make('Personnaliser')
                                ->schema([
                                    Forms\Components\TextInput::make('custom_duration')
                                        ->label('Durée')
                                        ->numeric()
                                        ->reactive()
                                        ->minValue(0)
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            if ($state !== null && $state !== '') {
                                                $set('formule_id', null);
                                                // Si le toggle crédit est actif, recalculez la réduction
                                                if ($get('utiliser_credit_pour_payer')) {
                                                    $client = Client::find($get('client_id'));
                                                    $soldeCredit = $client?->solde_credit ?? 0;
                                                    $totalSansCredits = self::getTotalSansCredits($get);
                                                    $discount = min($soldeCredit, $totalSansCredits);
                                                    $set('nombre_credits', -$discount);
                                                    $set('placeholder_credit', 'Réduction appliquée : -' . number_format($discount, 2, ',', ' ') . ' €');
                                                    $set('moyen_paiement', 'credit');
                                                }
                                            }
                                            self::updateTotal($set, $get);
                                        })
                                        ->columnSpan(1),

                                    Forms\Components\ToggleButtons::make('custom_unit')
                                        ->label('Unité')
                                        ->options([
                                            'heures' => 'Heures',
                                            'jours'  => 'Jours',
                                        ])
                                        ->inline()
                                        ->default('heures')
                                        ->columnSpan(2)
                                        ->afterStateUpdated(fn($state, Set $set, Get $get) => self::updateTotal($set, $get)),
                                ])
                                ->columns(3),
                        ]),
                ])
                ->columnSpan(['lg' => 1]),

            // Liste des services (champ caché)
            Forms\Components\Hidden::make('service_ids')
                ->default(fn () => request('service_ids', ''))
                ->dehydrated(false),

            Section::make('Services')
                ->schema(function (Get $get, Set $set) {
                    $serviceIds = array_filter(explode(',', $get('service_ids') ?? ''));
                    if (empty($serviceIds)) {
                        return [
                            Placeholder::make('message')
                                ->label('Aucun service sélectionné')
                                ->content("Aucun service n'a été trouvé."),
                        ];
                    }
                    $services = Service::whereIn('id', $serviceIds)->get();
                    if ($services->isNotEmpty()) {
                        $keyValueData = $services->mapWithKeys(fn ($service) => [
                            $service->nom => number_format($service->prix, 2) . ' €',
                        ])->toArray();
                        $total = $services->sum('prix');
                        $set('placeholder_services_default', 'Services : ' . number_format($total, 2, ',', ' ') . ' €');
                        return [
                            KeyValue::make('services')
                                ->hiddenLabel()
                                ->default($keyValueData)
                                ->addable(false)
                                ->editableKeys(false)
                                ->editableValues(false)
                                ->afterStateUpdated(function ($state, Set $set) {
                                    $total = array_sum(array_map(fn ($value) => (float) str_replace([' €', ','], ['', '.'], $value), $state ?? []));
                                    $set('placeholder_services', 'Services : ' . number_format($total, 2, ',', ' ') . ' €');
                                })
                                ->keyLabel('Nom du service')
                                ->valueLabel('Prix'),
                        ];
                    }
                    return [
                        Placeholder::make('message')
                            ->label('Aucun service sélectionné')
                            ->content("Aucun service n'a été trouvé."),
                    ];
                })
                ->hidden(fn (Get $get) => empty($get('service_ids')))
                ->columns(1),
        ];
    }

    public static function getPaiementForm(): Forms\Components\Group
    {
        return Forms\Components\Group::make()
            ->schema([
                // Section Crédit
                Section::make('Crédit')
                    ->schema([
                        Forms\Components\TextInput::make('nombre_credits')
                            ->label('Nombre de crédits à ajouter')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->columnSpan(4)
                            ->debounce(500)
                            ->disabled(fn (Get $get) => (bool) $get('utiliser_credit_pour_payer'))
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                // Si le toggle n'est pas actif et que l'utilisateur a saisi une valeur négative (par copier/coller par exemple), on force la valeur positive
                                if (!$get('utiliser_credit_pour_payer') && $state < 0) {
                                    $set('nombre_credits', abs($state));
                                }
                                // Dès qu'un montant est saisi manuellement, on désactive le toggle
                                if (!$get('utiliser_credit_pour_payer') && (int)$state !== 0) {
                                    $set('utiliser_credit_pour_payer', false);
                                }
                                // Mise à jour du coût pour crédits en cas d'achat
                                $creditsDemandes = max(0, (int)$state);
                                $reduction     = self::calculateCreditReduction($creditsDemandes);
                                $costForCredits = $creditsDemandes > 0 ? $creditsDemandes - $reduction : 0;
                                $set('placeholder_credit', $creditsDemandes > 0
                                    ? 'Crédit (achat) : ' . number_format($costForCredits, 2, ',', ' ') . ' €'
                                    : null
                                );
                                self::updateTotal($set, $get);
                            }),
                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Placeholder::make('solde_credit')
                                    ->label('Solde actuel')
                                    ->content(fn (Get $get) => number_format(optional(Client::find($get('client_id')))->solde_credit ?? 0, 2, ',', ' ')),
                                Forms\Components\Toggle::make('utiliser_credit_pour_payer')
                                    ->label('Utiliser pour payer')
                                    ->default(false)
                                    ->reactive()
                                    ->hidden(fn (Get $get) => (optional(Client::find($get('client_id')))->solde_credit ?? 0) <= 0)
                                    // Désactivation si l'utilisateur a saisi manuellement un montant positif
                                    ->disabled(fn (Get $get) => (int)$get('nombre_credits') > 0)
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if ($state) {
                                            $client = Client::find($get('client_id'));
                                            $soldeCredit = $client?->solde_credit ?? 0;
                                            $totalSansCredits = self::getTotalSansCredits($get);
                                            $discount = min($soldeCredit, $totalSansCredits);
                                            $set('nombre_credits', -$discount);
                                            $set('placeholder_credit', 'Réduction appliquée : -' . number_format($discount, 2, ',', ' ') . ' €');
                                            // Sélection automatique du moyen de paiement "Crédit"
                                            $set('moyen_paiement', 'credit');
                                        } else {
                                            $set('nombre_credits', 0);
                                            $set('placeholder_credit', null);
                                        }
                                        self::updateTotal($set, $get);
                                    }),
                            ]),
                    ])
                    ->columns(5),

                // Section Paiement
                Section::make('Paiement')
                    ->schema([
                        Section::make()
                            ->schema([
                                Forms\Components\Placeholder::make('placeholder_produits')
                                    ->hiddenLabel()
                                    ->hidden(fn (Get $get) => array_sum(array_map(fn ($produit) => $produit['prix'] ?? 0, $get('produits') ?? [])) == 0)
                                    ->content(fn (Get $get) => 'Produits : ' . number_format(
                                        array_sum(array_map(fn ($produit) => $produit['prix'] ?? 0, $get('produits') ?? [])),
                                        2,
                                        ',',
                                        ''
                                    ) . ' €'),
                                Forms\Components\Placeholder::make('placeholder_services')
                                    ->hiddenLabel()
                                    ->hidden(fn (Get $get) => (float) ($get('placeholder_services_default') ?? 0) == 0)
                                    ->content(fn (Get $get) => $get('placeholder_services'))
                                    ->default(fn (Get $get) => $get('placeholder_services_default') ?? 'Services : 0 €'),
                                Forms\Components\Placeholder::make('placeholder_formule')
                                    ->hiddenLabel()
                                    ->hidden(function (Get $get) {
                                        $formuleId      = $get('formule_id');
                                        $customDuration = $get('custom_duration');
                                        $customUnit     = $get('custom_unit');

                                        if ($formuleId) {
                                            $formule = Formule::find($formuleId);
                                            return !$formule || $formule->prix == 0;
                                        }
                                        if ($customDuration && $customUnit) {
                                            $tarif = Tarif::first();
                                            $prixUnitaire = $customUnit === 'heures' ? $tarif->prix_une_heure : $tarif->prix_un_jour;
                                            return ($customDuration * $prixUnitaire) == 0;
                                        }
                                        return true;
                                    })
                                    ->content(function (Get $get) {
                                        if ($formuleId = $get('formule_id')) {
                                            $formule = Formule::find($formuleId);
                                            return $formule
                                                ? "Formule : " . number_format($formule->prix, 2, ',', ' ') . " €"
                                                : 'Formule non valide';
                                        }
                                        if ($customDuration = $get('custom_duration')) {
                                            if ($customUnit = $get('custom_unit')) {
                                                $tarif = Tarif::first();
                                                $prixUnitaire = $customUnit === 'heures' ? $tarif->prix_une_heure : $tarif->prix_un_jour;
                                                $total = $customDuration * $prixUnitaire;
                                                return "Formule : " . number_format($total, 2, ',', ' ') . " €";
                                            }
                                        }
                                        return 'Formule : 0 €';
                                    }),
                                Forms\Components\Placeholder::make('placeholder_credit')
                                    ->hiddenLabel()
                                    ->hidden(fn (Get $get) => ($get('nombre_credits') ?? 0) == 0)
                                    ->content(function (Get $get) {
                                        $nombreCredits = $get('nombre_credits') ?? 0;
                                        if ($nombreCredits < 0) {
                                            return 'Crédit : -' . number_format(abs((float)$nombreCredits), 2, ',', ' ') . ' €';
                                        }
                                        $creditsDemandes = (int) $nombreCredits;
                                        $reduction = self::calculateCreditReduction($creditsDemandes);
                                        $prixTotal = $creditsDemandes > 0 ? $creditsDemandes - $reduction : 0;
                                        return 'Crédit : ' . number_format(max($prixTotal, 0), 2, ',', ' ') . ' €';
                                    }),
                                Forms\Components\Placeholder::make('placeholder_total')
                                    ->label('TOTAL :')
                                    ->reactive()
                                    ->content(fn (Get $get) => self::calculateTotal($get)),
                            ])
                            ->columnSpan(1),
                        Section::make('')
                            ->schema([
                                ToggleButtons::make('moyen_paiement')
                                    ->label('Moyen de paiement')
                                    ->options(fn (Get $get) => (
                                        ($get('utiliser_credit_pour_payer') &&
                                         (abs((float)($get('nombre_credits') ?? 0)) >= self::getTotalSansCredits($get))
                                        )
                                        ? ['credit' => 'Crédit']
                                        : ['carte' => 'Carte bancaire', 'espece' => 'Espèces']
                                    ))
                                    ->default(fn (Get $get) => (
                                        ($get('utiliser_credit_pour_payer') &&
                                         (abs((float)($get('nombre_credits') ?? 0)) >= self::getTotalSansCredits($get))
                                        )
                                        ? 'credit'
                                        : null
                                    ))
                                    ->inline()
                                    ->required()
                                    ->reactive(),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Calcule le total hors crédits (produits + services + formule/personnalisation).
     */
    private static function getTotalSansCredits(Get $get): float
    {
        $totalProduits = array_sum(array_map(fn ($produit) => $produit['prix'] ?? 0, $get('produits') ?? []));
        $total = $totalProduits + (float) ($get('placeholder_services_default') ?? 0);

        if ($formuleId = $get('formule_id')) {
            $formule = Formule::find($formuleId);
            $total += $formule ? $formule->prix : 0;
        }

        if ($customDuration = $get('custom_duration')) {
            if ($customUnit = $get('custom_unit')) {
                $tarif = Tarif::first();
                $prixUnitaire = $customUnit === 'heures' ? $tarif->prix_une_heure : $tarif->prix_un_jour;
                $total += $customDuration * $prixUnitaire;
            }
        }

        return $total;
    }

    /**
     * Pour un nombre de crédits demandés, renvoie la réduction applicable.
     */
    private static function calculateCreditReduction(int $creditsDemandes): float
    {
        $reduction = 0;
        foreach (Credit::orderByDesc('montant')->get() as $palier) {
            if ($creditsDemandes >= $palier->montant) {
                $reduction = $palier->montant - $palier->prix;
                break;
            }
        }
        return $reduction;
    }

    /**
     * Calcule le total global en fonction du total hors crédits et de l'utilisation ou non des crédits.
     */
    private static function calculateTotal(Get $get): string
    {
        $totalSansCredits = self::getTotalSansCredits($get);

        if ((bool) ($get('utiliser_credit_pour_payer') ?? false)) {
            $discount = abs((float) ($get('nombre_credits') ?? 0));
            $discount = min($discount, $totalSansCredits);
            $total = $totalSansCredits - $discount;
        } else {
            $creditsDemandes = max(0, (int) ($get('nombre_credits') ?? 0));
            $reduction = self::calculateCreditReduction($creditsDemandes);
            $costForCredits = $creditsDemandes > 0 ? $creditsDemandes - $reduction : 0;
            $total = $totalSansCredits + $costForCredits;
        }

        return number_format($total, 2, ',', ' ') . ' €';
    }

    /**
     * Met à jour le placeholder du total.
     */
    private static function updateTotal(Set $set, Get $get): void
    {
        $set('placeholder_total', self::calculateTotal($get));
    }
}
