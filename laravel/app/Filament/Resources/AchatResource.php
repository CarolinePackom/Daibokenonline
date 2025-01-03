<?php

namespace App\Filament\Resources;

use App\Enums\StatutEnum;
use App\Filament\Resources\AchatResource\Pages;
use App\Filament\Resources\AchatResource\RelationManagers;
use App\Models\Achat;
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

class AchatResource extends Resource
{
    protected static ?string $model = Achat::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema(static::getDetailsFormSchema())
                            ->columns(2),

                        Forms\Components\Section::make('Order items')
                            ->headerActions([
                                Action::make('reset')
                                    ->modalHeading('Are you sure?')
                                    ->modalDescription('All existing items will be removed from the order.')
                                    ->requiresConfirmation()
                                    ->color('danger')
                                    ->action(fn (Forms\Set $set) => $set('items', [])),
                            ])
                            ->schema([
                                static::getProduitsRepeater(),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn (?Achat $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn (Achat $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn (Achat $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn (?Achat $record) => $record === null),
            ])
            ->columns(3);
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
            'index' => Pages\ListAchats::route('/'),
            'create' => Pages\CreateAchat::route('/create'),
            'edit' => Pages\EditAchat::route('/{record}'),
        ];
    }

    public static function getDetailsFormSchema(): array
    {
        return [
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
                ->default(fn () => request('client_id'))
                ->required()
                ->placeholder('Sélectionnez un client'),

            ToggleButtons::make('statut')
                ->label('Statut')
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

            Forms\Components\Section::make('Produits')
                ->schema([
                    static::getProduitsRepeater(),
                ]),

            Forms\Components\Section::make('Formule')
                ->schema([
                    static::getFormuleForm(),
                ]),
        ];
    }

    public static function getProduitsRepeater(): Repeater
    {
        return Repeater::make('produits')
            ->relationship('produits')
            ->schema([
                Forms\Components\Select::make('produit_id')
                    ->label('Produit')
                    ->options(Produit::query()->pluck('nom', 'id'))
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $prixUnitaire = Produit::find($state)?->prix ?? 0;
                        $quantite = $get('quantite') ?? 1;
                        $set('prix', $prixUnitaire * $quantite);
                    })
                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->searchable(),

                Forms\Components\TextInput::make('quantite')
                    ->label('Quantité')
                    ->numeric()
                    ->default(1)
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $prixUnitaire = Produit::find($get('produit_id'))?->prix ?? 0;
                        $set('prix', $prixUnitaire * $state);
                    })
                    ->columnSpan([
                        'md' => 2,
                    ]),

                Forms\Components\TextInput::make('prix')
                    ->label('Prix')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->default(0)
                    ->columnSpan([
                        'md' => 3,
                    ]),
            ])
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip('Open product')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);

                        $product = Produit::find($itemData['produit_id']);

                        if (! $product) {
                            return null;
                        }

                        return ProduitResource::getUrl('edit', ['record' => $product]);
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['produit_id'])),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ]);
    }

    public static function getFormuleForm(): Forms\Components\Group
    {
        return Forms\Components\Group::make()
            ->schema([
                Forms\Components\ToggleButtons::make('formule_id')
                    ->label('')
                    ->options(
                        Formule::query()
                            ->pluck('nom', 'id')
                            ->toArray() + ['custom' => 'Personnalisée']
                    )
                    ->reactive()
                    ->inline(),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('custom_value')
                            ->label('Nombre')
                            ->numeric()
                            ->placeholder('Saisissez un nombre')
                            ->required(),

                        Forms\Components\ToggleButtons::make('custom_unit')
                            ->label('Unité')
                            ->options([
                                'jours' => 'Jours',
                                'heures' => 'Heures',
                            ])
                            ->inline(),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('formule_id') === 'custom'),
            ]);
    }

    public static function getPaiementForm(): Forms\Components\Group
{
    $paliers = \App\Models\Credit::query()->orderBy('montant', 'desc')->get();
    return Forms\Components\Group::make()
        ->schema([
            Forms\Components\Section::make('Crédit')
                ->schema([

                    Forms\Components\TextInput::make('nombre_credits')
                        ->label('Nombre de crédits à ajouter')
                        ->numeric()
                        ->reactive()
                        ->default(0)
                        ->hint(fn (Forms\Get $get) => self::calculPrixFrontend($get('nombre_credits'), $paliers)),

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
        $prixCredits = (float) self::calculPrixFrontend($credits, \App\Models\Credit::query()->orderBy('montant', 'desc')->get());

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

protected static function calculPrixFrontend(float $creditsDemandes, $paliers): string
{
    $prixTotal = $creditsDemandes;
    foreach ($paliers as $palier) {
        if ($creditsDemandes >= $palier->montant) {
            $reduction = $palier->montant - $palier->prix;
            $prixTotal -= $reduction;
            break;
        }
    }
    return $creditsDemandes > 0
        ? 'Prix total : ' . number_format(max($prixTotal, 0), 2) . ' €'
        : 'Veuillez entrer le nombre de crédits.';
}



}
