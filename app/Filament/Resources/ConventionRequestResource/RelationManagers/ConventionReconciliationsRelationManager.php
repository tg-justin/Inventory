<?php

namespace App\Filament\Resources\ConventionRequestResource\RelationManagers;

use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

// Import Location

// Use Fieldset for grouping

// Import Model

class ConventionReconciliationsRelationManager extends RelationManager
{
	protected static string $relationship = 'conventionReconciliations';

	protected static ?string $recordTitleAttribute = 'item.name';

	public function form(Form $form): Form
	{
		// Form for entering reconciliation data for ONE item from the request
		return $form
			->schema([
						 Forms\Components\Select::make('item_id')
												->label('Item')
							 // Limit to items actually ON this request
												->relationship(
								 'item',
								 'name',
								 // Modify query to only show items belonging to the parent request's items
								 // This requires accessing the owner record (the ConventionRequest)
								 fn(RelationManager $livewire) => $livewire->ownerRecord->conventionRequestItems()->select('item_id')
							 )
												->options(function (RelationManager $livewire): array {
													// Fetch only items that were part of the original request
													$requestItems = $livewire->ownerRecord->conventionRequestItems()->with('item')->get();

													return $requestItems->pluck('item.name', 'item.id')->toArray();
												})
												->searchable()
												->required()
												->disabled() // Typically disabled - reconciliation is based on shipped items
												->dehydrated(), // Ensure it saves even if disabled
						 //->columnSpan(2),

						 Forms\Components\TextInput::make('quantity_shipped')
												   ->label('Quantity Shipped')
												   ->numeric()
							 // Set default based on the selected item's shipped quantity on the request
												   ->default(function (callable $get, RelationManager $livewire): ?int {
								 $itemId = $get('item_id');
								 if (!$itemId) return NULL;

								 return $livewire->ownerRecord
											->conventionRequestItems()
											->where('item_id', $itemId)
											->first()?->quantity_shipped ?? 0;
							 })
												   ->disabled() // Read-only, based on shipping data
												   ->dehydrated() // Ensure it saves even if disabled
												   ->required(),

						 Fieldset::make('Post-Convention Counts')
								 ->schema([
											  Forms\Components\TextInput::make('quantity_used')
																		->label('Used / Given Away')
																		->numeric()
																		->required()
																		->default(0)
																		->minValue(0),
											  Forms\Components\TextInput::make('quantity_returned_to_storage')
																		->label('Returned to Storage')
																		->numeric()
																		->required()
																		->default(0)
																		->minValue(0)
																		->reactive(), // Make reactive for conditional field
											  Forms\Components\Select::make('return_location_id')
																	 ->label('Return Location')
																	 ->options(Location::where('is_personal_cache', FALSE)->pluck('name', 'id')) // Example: Only main storage
																	 ->searchable()
												  // Required only if quantity returned is > 0
																	 ->required(fn(callable $get) => $get('quantity_returned_to_storage') > 0)
																	 ->visible(fn(callable $get) => $get('quantity_returned_to_storage') > 0), // Show only if needed
											  Forms\Components\TextInput::make('quantity_kept_by_requester')
																		->label('Kept by Requester')
																		->numeric()
																		->required()
																		->default(0)
																		->minValue(0)
																		->reactive(),
											  Forms\Components\Select::make('requester_kept_location_id')
																	 ->label('Requester Cache')
												  // Show only personal caches, maybe default to the requester's if identifiable
																	 ->options(Location::where('is_personal_cache', TRUE)->pluck('name', 'id'))
																	 ->searchable()
																	 ->required(fn(callable $get) => $get('quantity_kept_by_requester') > 0)
																	 ->visible(fn(callable $get) => $get('quantity_kept_by_requester') > 0),
											  Forms\Components\TextInput::make('quantity_lost_damaged')
																		->label('Lost / Damaged')
																		->numeric()
																		->required()
																		->default(0)
																		->minValue(0),
										  ])->columns(3), // Use columns within the fieldset

						 Forms\Components\DatePicker::make('reconciliation_date')
													->required()
													->default(now())
													->columnSpanFull(),

						 Forms\Components\Select::make('reconciler_id')
												->label('Reconciled By')
												->relationship('reconciler', 'name')
												->searchable()
												->required()
												->default(auth()->id()) // Default to logged-in user
												->columnSpanFull(),

						 Forms\Components\Textarea::make('notes')
												  ->label('Reconciliation Notes')
												  ->nullable()
												  ->columnSpanFull(),

						 // TODO: Add custom validation rule to ensure used + returned + kept + lost == shipped
					 ])->columns(2);
	}

	public function table(Table $table): Table
	{
		// Table showing reconciliation results
		return $table
			// ->recordTitleAttribute('item.name')
			->columns([
						  Tables\Columns\TextColumn::make('item.name')
												   ->label('Item')
												   ->searchable()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('item.sku')
												   ->label('SKU')
												   ->searchable()
												   ->toggleable(isToggledHiddenByDefault: TRUE),
						  Tables\Columns\TextColumn::make('quantity_shipped')->label('Shipped')->numeric()->sortable(),
						  Tables\Columns\TextColumn::make('quantity_used')->label('Used')->numeric()->sortable(),
						  Tables\Columns\TextColumn::make('quantity_returned_to_storage')->label('Returned')->numeric()->sortable(),
						  Tables\Columns\TextColumn::make('returnLocation.name')->label('Return Loc')->sortable()->placeholder('N/A'),
						  Tables\Columns\TextColumn::make('quantity_kept_by_requester')->label('Kept')->numeric()->sortable(),
						  Tables\Columns\TextColumn::make('requesterKeptLocation.name')->label('Kept Loc')->sortable()->placeholder('N/A'),
						  Tables\Columns\TextColumn::make('quantity_lost_damaged')->label('Lost')->numeric()->sortable(),
						  Tables\Columns\TextColumn::make('reconciliation_date')->label('Date')->sortable(),
						  Tables\Columns\TextColumn::make('reconciler.name')->label('Reconciler')->sortable()->toggleable(isToggledHiddenByDefault: TRUE),
					  ])
			->filters([
						  //
					  ])
			->headerActions([
								// CreateAction might be disabled or removed here. Reconciliation is often a
								// specific workflow step initiated elsewhere (e.g., a button on the main request page).
								// If enabled, it should probably only show items that were shipped but not yet reconciled.
								// Tables\Actions\CreateAction::make(),
							])
			->actions([
						  Tables\Actions\EditAction::make(), // To correct reconciliation entry
						  Tables\Actions\DeleteAction::make(), // To remove incorrect entry
					  ])
			->bulkActions([
							  Tables\Actions\BulkActionGroup::make([
																	   Tables\Actions\DeleteBulkAction::make(),
																   ]),
						  ]);
	}

	// Add logic to disable Create button if request status isn't suitable for reconciliation
	// public static function canCreate(RelationManager $livewire): bool
	// {
	//     return $livewire->ownerRecord->status === 'received' || $livewire->ownerRecord->status === 'reconciling';
	// }

	// Similarly disable edit/delete based on status if needed
}