<?php

namespace App\Filament\Resources;

use App\Enums\StatutEnum;
use App\Filament\Resources\VenteResource\Pages;
use App\Filament\Resources\VenteResource\RelationManagers;
use App\Models\Vente;
use App\Models\Client;
use App\Models\Formule;
use App\Models\Produit;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Filament\Forms\Get;

class VenteResource extends Resource
{
    protected static ?string $model = Vente::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

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
                Tables\Columns\TextColumn::make('statut.nom'),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('est_paye')
                    ->boolean(),
                Tables\Columns\TextColumn::make('moyen_paiement'),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre_credits')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('formule_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre_heures')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nombre_jours')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListVentes::route('/'),
            'create' => Pages\CreateVente::route('/create'),
            'edit' => Pages\EditVente::route('/{record}'),
        ];
    }

    public static function getCreationFormSchema(?int $clientId = null): array
    {
        $resolvedClientId = request('client_id') ?? $clientId;
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('')
                        ->schema([
                            Forms\Components\Select::make('client_id')
                                ->label('Client')
                                ->options(
                                    fn () => Client::query()
                                        ->whereNull('archived_at')
                                        ->get()
                                        ->mapWithKeys(fn ($client) => [
                                            $client->id => ucfirst(strtolower($client->prenom)) . ' ' . ucfirst(strtolower($client->nom)),
                                        ])
                                )
                                ->searchable()
                                ->prefixIcon('heroicon-m-user')
                                ->default($resolvedClientId)
                                ->disabled($resolvedClientId !== null)
                                ->required()
                                ->placeholder('Sélectionnez un client')
                        ]),

                    Repeater::make('produits')
                        ->schema([
                            Forms\Components\Select::make('produit_id')
                                ->label('Nom')
                                ->options(Produit::query()->pluck('nom', 'id'))
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $prixUnitaire = Produit::find($state)?->prix ?? 0;
                                    $quantite = $get('quantite') ?? 1;
                                    $set('prix', $prixUnitaire * $quantite);
                                })
                                ->distinct()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->columnSpan(['lg' => 2])
                                ->placeholder('Sélectionnez un produit')
                                ->nullable()
                                ->searchable(),

                            Forms\Components\TextInput::make('quantite')
                                ->label('Quantité')
                                ->numeric()
                                ->default(1)
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $prixUnitaire = Produit::find($get('produit_id'))?->prix ?? 0;
                                    $set('prix', $prixUnitaire * $state);
                                })
                                ->live(onBlur: true)
                                ->columnSpan(['lg' => 1]),

                            Forms\Components\TextInput::make('prix')
                                ->label('Prix')
                                ->disabled()
                                ->dehydrated()
                                ->numeric()
                                ->default(0)
                                ->columnSpan(['lg' => 1]),
                        ])
                        ->defaultItems(1)
                        ->hiddenLabel()
                        ->addActionLabel('Ajouter un produit')
                        ->reorderable(false)
                        ->itemLabel("Produit")
                        ->columns(4)
                ])
                ->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Statut')
                        ->schema([
                            ToggleButtons::make('statut')
                                ->hiddenLabel()
                                ->options(
                                    collect(StatutEnum::cases())
                                        ->mapWithKeys(fn (StatutEnum $statut) => [$statut->value => $statut->getLabel()])
                                        ->toArray()
                                )
                                ->colors(
                                    collect(StatutEnum::cases())
                                        ->mapWithKeys(fn (StatutEnum $statut) => [$statut->value => $statut->getColor()])
                                        ->toArray()
                                )
                                ->icons(
                                    collect(StatutEnum::cases())
                                        ->mapWithKeys(fn (StatutEnum $statut) => [$statut->value => $statut->getIcon()])
                                        ->toArray()
                                )
                                ->required()
                                ->inline()
                                ->default(StatutEnum::Pret->value),


                        ]),


                        Forms\Components\Tabs::make()
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('Formules')
                                    ->schema([
                                        Forms\Components\ToggleButtons::make('formule_id')
                                            ->label('')
                                            ->options(
                                                Formule::query()
                                                    ->pluck('nom', 'id')
                                            )
                                            ->gridDirection('row')
                                            ->disabled(fn (Forms\Get $get) => $get('custom_duration') !== null && $get('custom_duration') > 0)
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state !== null) {
                                                    $set('custom_duration', null);
                                                }
                                            })
                                            ->columns(2),
                                    ]),

                                Forms\Components\Tabs\Tab::make('Personnalisée')
                                    ->schema([
                                        Forms\Components\TextInput::make('custom_duration')
                                            ->label('Durée')
                                            ->numeric()
                                            ->reactive()
                                            ->minValue(0)
                                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                if ($state !== null && $state !== '') {
                                                    $set('formule_id', null);
                                                }
                                            })
                                            ->columnSpan(1),

                                        Forms\Components\ToggleButtons::make('custom_unit')
                                            ->label('Unité')
                                            ->options([
                                                'heures' => 'Heures',
                                                'jours' => 'Jours',
                                            ])
                                            ->inline()
                                            ->default('heures')
                                            ->columnSpan(2),
                                    ])
                                    ->columns(3),
                            ]),

                ])
                ->columnSpan(['lg' => 1]),

            Forms\Components\Hidden::make('service_ids')
                ->default(fn () => request('service_ids', ''))
                ->dehydrated(false),

            Section::make('Services')
                ->schema(function (Forms\Get $get, Forms\Set $set) {
                    $serviceIds = explode(',', $get('service_ids') ?? '');

                    if (empty($serviceIds)) {
                        return [
                            Placeholder::make('message')
                                ->label('Aucun service sélectionné')
                                ->content('Aucun service n\'a été trouvé.'),
                        ];
                    }

                    $services = Service::whereIn('id', $serviceIds)->get();

                    if ($services->isNotEmpty()) {
                        $keyValueData = $services->mapWithKeys(function ($service) {
                            return [
                                $service->nom => number_format($service->prix, 2) . ' €',
                            ];
                        })->toArray();

                        $total = $services->sum('prix');
                        $set('placeholder_services_default', 'Services : ' . number_format($total, 2, ',') . ' €');

                        return [
                            KeyValue::make('services')
                                ->hiddenLabel()
                                ->default($keyValueData)
                                ->addable(false)
                                ->editableKeys(false)
                                ->editableValues(false)
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $total = array_sum(array_map(function ($value) {
                                        return (float) str_replace([' €', ','], ['', '.'], $value);
                                    }, $state ?? []));
                                    $set('placeholder_services', 'Services : ' . number_format($total, 2, ',') . ' €');
                                })
                                ->keyLabel('Nom du service')
                                ->valueLabel('Prix'),
                        ];
                    }

                    return [
                        Placeholder::make('message')
                            ->label('Aucun service sélectionné')
                            ->content('Aucun service n\'a été trouvé.'),
                    ];
                })
                ->hidden(fn (Forms\Get $get) => empty($get('service_ids')))
                ->columns(1)
        ];
    }

    public static function getPaiementForm(): Forms\Components\Group
    {
        return Forms\Components\Group::make()
            ->schema([
                Forms\Components\Section::make('Crédit')
                    ->schema([
                        Forms\Components\TextInput::make('nombre_credits')
                            ->label('Nombre de crédits à ajouter')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->hint(static::getHintHtml(
                                \App\Models\Credit::query()
                                    ->orderBy('montant', 'desc')
                                    ->get(['montant', 'prix'])
                                    ->toArray()
                            ))
                            ->columnSpan(4),

                        Forms\Components\Placeholder::make('solde_credit')
                            ->label('Solde actuel')
                            ->content(fn (Forms\Get $get) =>
                                    Client::find($get('client_id'))?->solde_credit
                                        ? number_format(Client::find($get('client_id'))?->solde_credit, 2, ',', ' ')
                                        : '0,00'
                                ),
                    ])
                    ->columns(5),

                Forms\Components\Section::make('Paiement')
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Placeholder::make('placeholder_produits')
                                    ->hiddenLabel()
                                    ->content(fn (Forms\Get $get) =>
                                        'Produits : ' . (array_sum(array_map(fn ($produit) => $produit['prix'] ?? 0, $get('produits') ?? [])) == 0
                                        ? '0 €'
                                        : number_format(array_sum(array_map(fn ($produit) => $produit['prix'] ?? 0, $get('produits') ?? [])), 2, ',', '') . ' €')
                                    ),
                                Forms\Components\Placeholder::make('placeholder_services')
                                    ->hiddenLabel()
                                    ->content(fn (Forms\Get $get) => $get('placeholder_services'))
                                    ->default(fn (Forms\Get $get) => $get('placeholder_services_default') ?? 'Services : 0 €'),
                                Forms\Components\Placeholder::make('placeholder_formule')
                                    ->hiddenLabel()
                                    ->content(function (Forms\Get $get) {
                                        $tarif = \App\Models\Tarif::first();

                                        $formuleId = $get('formule_id');
                                        if ($formuleId) {
                                            $formule = \App\Models\Formule::find($formuleId);
                                            return $formule ? "Formule : " . number_format($formule->prix, 2, ',') . " €" : 'Formule non valide';
                                        }

                                        $customDuration = $get('custom_duration');
                                        $customUnit = $get('custom_unit');
                                        if ($customDuration && $customUnit) {
                                            $prixUnitaire = $customUnit === 'heures' ? $tarif->prix_une_heure : $tarif->prix_un_jour;
                                            $total = $customDuration * $prixUnitaire;
                                            return "Formule : " . number_format($total, 2, ',') . " €";
                                        }

                                        return 'Formule : 0 €';
                                    }),
                                Placeholder::make('placeholder_credit')
                                    ->hiddenLabel()
                                    ->content('Crédit : 0 €'),
                                Placeholder::make('placeholder_total')
                                    ->hiddenLabel()
                                    ->content('TOTAL : 0 €'),
                            ])
                            ->columnSpan(1),

                        Forms\Components\Section::make('')
                            ->schema([
                                Forms\Components\ToggleButtons::make('moyen_paiement')
                                    ->label('Moyen de paiement')
                                    ->options([
                                        'carte' => 'Carte bancaire',
                                        'espece' => 'Espèces',
                                        'credit' => 'Crédit',
                                    ])
                                    ->required()
                                    ->visible(fn (Forms\Get $get) => $get('moyen_paiement') !== 'credit' || ($get('client_credit') >= $get('total'))),
                            ])
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getHintHtml(array $paliers): HtmlString
    {
        $paliersJson = json_encode($paliers);
        $index = null;
        $value = null;

        return new HtmlString("
    <div>
        <span id=\"hint_nombre_credits\">Prix : 0 €</span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hintElement = document.getElementById('hint_nombre_credits');
            const nombreCreditsInput = document.getElementById('data.nombre_credits');
            const placeholders = Array.from(document.querySelectorAll('.fi-fo-placeholder'));
            const placeholdersExceptLast = placeholders.slice(0, -1);

            let total = 0;
            let paliers = $paliersJson;

            nombreCreditsInput.addEventListener('input', update);

            setInterval(update, 500);

            function update(){
                updateHint();
                updatePlaceholderCredit();
                updatePlaceholderTotal();
            }

            function updateHint() {
                hintElement.innerHTML = 'Prix : ' + calculPrixCredit() + ' €';
            }

            function updatePlaceholderCredit() {
                let totalCredits = calculPrixCredit();

                const creditPlaceholder = document.querySelector('[for=\'data.placeholder_credit\']').nextElementSibling.querySelector('.fi-fo-placeholder');
                if (creditPlaceholder) {
                    creditPlaceholder.textContent = 'Crédit : ' + totalCredits + ' €';
                }
            }

            function updatePlaceholderTotal(){
                const total = calculTotal();

                const totalPlaceholder = document.querySelector('[for=\'data.placeholder_total\']').nextElementSibling.querySelector('.fi-fo-placeholder');
                if (totalPlaceholder) {
                    totalPlaceholder.innerHTML = '<strong>TOTAL : ' + total.toFixed(2).replace('.', ',') + ' €</strong>';
                }
            }

            function calculPrixCredit() {
                let creditsDemandes = parseFloat(nombreCreditsInput.value) || 0;
                let prixTotal = creditsDemandes;
                for (const palier of paliers) {
                    if (creditsDemandes >= palier.montant) {
                        const reduction = palier.montant - palier.prix;
                        prixTotal -= reduction;
                        break;
                    }
                }
                return creditsDemandes > 0 ? Math.max(prixTotal, 0).toFixed(2).replace('.', ',') : 0;
            }

            function calculTotal() {
    let total = 0;
    placeholdersExceptLast.forEach((placeholder) => {
        const text = placeholder.textContent.trim();
        const match = text.match(/[\d,\.]+/);
        if (match) {
            total += parseFloat(match[0].replace(',', '.'));
        }
    });
    return parseFloat(total);
}

        });
    </script>
");
}
}
