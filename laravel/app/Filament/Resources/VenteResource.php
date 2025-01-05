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
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use PHPUnit\Metadata\Group;

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

    public static function getCreationFormSchema(): array
    {
        $index = 0;
        return [
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Client')
                        ->schema([
                            Forms\Components\Select::make('client_id')
                                ->hiddenLabel()
                                ->options(
                                    fn () => Client::query()
                                        ->whereNull('archived_at')
                                        ->get()
                                        ->mapWithKeys(fn ($client) => [
                                            $client->id => ucfirst(strtolower($client->prenom)) . ' ' . ucfirst(strtolower($client->nom)),
                                        ])
                                )
                                ->searchable()
                                ->default(fn () => request('client_id'))
                                ->required()
                                ->placeholder('Sélectionnez un client')
                        ]),

                    Repeater::make('produits')
                        ->relationship('produits')
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
                                            ->columns(2),
                                    ]),

                                Forms\Components\Tabs\Tab::make('Personnalisée')
                                    ->schema([
                                        Forms\Components\TextInput::make('custom_duration')
                                            ->label('Durée')
                                            ->numeric()
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





            Forms\Components\Fieldset::make('Services')
                ->schema(function () {
                    $serviceIds = explode(',', request('service_ids', ''));

                    $services = Service::whereIn('id', $serviceIds)->get();

                    if ($services->isNotEmpty()) {
                        return $services->map(function ($service) {
                            return Forms\Components\Group::make()
                                ->schema([
                                    Forms\Components\Checkbox::make("services[{$service->id}][selected]")
                                        ->label('')
                                        ->default(true)
                                        ->columnSpan(1),

                                    Forms\Components\Placeholder::make("services[{$service->id}][name]")
                                        ->label('')
                                        ->content($service->nom)
                                        ->columnSpan(3),

                                    Forms\Components\Placeholder::make("services[{$service->id}][price]")
                                        ->label('')
                                        ->content(number_format($service->prix, 2) . ' €')
                                        ->columnSpan(2),
                                ])
                                ->columns(6)
                                ->inlineLabel(false);
                        })->toArray();
                    }

                    return [
                        Forms\Components\Placeholder::make('message')
                            ->label('Aucun service sélectionné')
                            ->content('Aucun service n\'a été trouvé.'),
                    ];
                })
                ->columns(1) // Chaque service s'affiche sur une ligne
                ->hidden(fn () => !request('service_ids')),
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
                        )),

                    KeyValue::make('services')
                ->label('Services')
                ->keyLabel('Nom du service')
                ->valueLabel('Prix du service')
                ->addActionLabel('Ajouter un service')
                ->reorderable()
                ->keyPlaceholder('Entrez le nom du service')
                ->valuePlaceholder('Entrez le prix du service')
                ->required(),

                ]),

            Forms\Components\Section::make('Paiement')
                ->schema([

                    Forms\Components\ToggleButtons::make('moyen_paiement')
                        ->label('Moyen de paiement')
                        ->options([
                            'carte' => 'Carte bancaire',
                            'espece' => 'Espèces',
                            'credit' => 'Crédit',
                        ])
                        ->reactive()
                        ->inline()
                        ->visible(fn (Forms\Get $get) => $get('moyen_paiement') !== 'credit' || ($get('client_credit') >= $get('total'))),


                    Forms\Components\Placeholder::make('recapitulatif')
    ->label('Récapitulatif')
    ->content(function (Forms\Get $get): HtmlString {
        // Récupération des montants
        $totalProduits = (float) array_sum(array_column($get('produits') ?? [], 'prix'));
        $totalServices = (float) array_sum(array_map(fn ($s) => $s['selected'] ? $s['price'] : 0, $get('services') ?? []));
        $formule = (float) (Formule::find($get('formule_id'))?->prix ?? 0);
        $credits = (float) ($get('nombre_credits') ?? 0);
        $prixCredits = 0;

        // Calcul du total
        $totalGeneral = $totalProduits + $totalServices + $formule + $prixCredits;

        // Récapitulatif formaté en HTML
        $html = "
            <ul>
                <li>Services : " . number_format($totalServices, 2) . " €</li>
                <li>Produits : " . number_format($totalProduits, 2) . " €</li>
                <li>Formule : " . number_format($formule, 2) . " €</li>
                <li>Crédit : " . number_format($prixCredits, 2) . " €</li>
            </ul>
            <p><strong>Total : " . number_format($totalGeneral, 2) . " €</strong></p>
        ";

        return new HtmlString($html);

                        }),
                ])

        ])
        ->columns(2);
}

public static function getHintHtml(array $paliers): HtmlString
{
    $paliersJson = json_encode($paliers); // Encode les paliers en JSON pour les utiliser en JavaScript.

    return new HtmlString("
        <div>
            <span id=\"hint_nombre_credits\">Veuillez entrer un nombre.</span>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const hintElement = document.getElementById('hint_nombre_credits');
                    const nombreCreditsInput = document.getElementById('data.nombre_credits');

                    if (!nombreCreditsInput || !hintElement) {
                        console.error('L\'élément attendu n\'a pas été trouvé dans le DOM.');
                        return;
                    }

                    const paliers = $paliersJson;

                    function calculPrixFrontend(creditsDemandes) {
                        let prixTotal = creditsDemandes;
                        for (const palier of paliers) {
                            if (creditsDemandes >= palier.montant) {
                                const reduction = palier.montant - palier.prix;
                                prixTotal -= reduction;
                                break;
                            }
                        }
                        return creditsDemandes > 0
                            ? 'Prix total : ' + Math.max(prixTotal, 0).toFixed(2) + ' €'
                            : 'Veuillez entrer un nombre de crédits.';
                    }

                    nombreCreditsInput.addEventListener('input', function () {
                        const creditsDemandes = parseFloat(this.value) || 0;
                        hintElement.innerHTML = calculPrixFrontend(creditsDemandes);
                    });
                });
            </script>
        </div>
    ");
}


}
