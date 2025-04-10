<?php

namespace App\Filament\Resources\ConventionRequestResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

// Import Item model

class ConventionRequestItemsRelationManager extends RelationManager
{
	protected static string $relationship = 'conventionRequestItems';

	protected static ?string $recordTitleAttribute = 'item.name'; // Use item name for identification

	public function form(Form $form): Form
	{
		// Form for adding/editing an item ON the request
		return $form
			->schema([
						 Forms\Components\Select::make('item_id')
												->label('Item')
												->relationship('item', 'name', fn(Builder $query) => $query->where('is_active', TRUE)) // Only show active items
												->searchable()
												->preload() // Preload for better performance if item list isn't huge
												->required()
							 // You could make this reactive to show current total stock if desired
							 // ->reactive()
							 // ->afterStateUpdated(fn ($state, callable $set) => $set('current_stock', Item::find($state)?->inventoryStock()->sum('quantity') ?? 0))
												->columnSpan(2), // Make it wider

						 // Forms\Components\Placeholder::make('current_stock')
						 //     ->label('Current Total Stock')
						 //     ->content(fn (callable $get) => $get('current_stock') ?? 'N/A'),

						 Forms\Components\TextInput::make('quantity_requested')
												   ->label('Quantity Requested')
												   ->numeric()
												   ->required()
												   ->minValue(1)
												   ->columnSpan(1),

						 Forms\Components\Textarea::make('notes')
												  ->label('Notes')
												  ->nullable()
												  ->columnSpan(2), // Span across columns

						 // NOTE: quantity_approved and quantity_shipped are usually handled
						 // by separate actions (Approve, Ship) on the main resource,
						 // so they are typically not included in this create/edit form.
					 ])->columns(3); // Use 3 columns for layout
	}

	public function table(Table $table): Table
	{
		// Table showing items included in this request
		return $table
			// ->recordTitleAttribute('item.name') // Already set above
			->columns([
						  Tables\Columns\TextColumn::make('item.name')
												   ->label('Item')
												   ->searchable()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('item.sku')
												   ->label('SKU')
												   ->searchable()
												   ->toggleable(isToggledHiddenByDefault: TRUE), // Optionally hide SKU
						  Tables\Columns\TextColumn::make('quantity_requested')
												   ->label('Requested')
												   ->numeric()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('quantity_approved')
												   ->label('Approved')
												   ->numeric()
												   ->sortable()
												   ->placeholder('Pending'), // Show placeholder if null
						  Tables\Columns\TextColumn::make('quantity_shipped')
												   ->label('Shipped')
												   ->numeric()
												   ->sortable()
												   ->placeholder('Pending'), // Show placeholder if null
						  Tables\Columns\TextColumn::make('notes')
												   ->label('Notes')
												   ->toggleable(isToggledHiddenByDefault: TRUE) // Hide notes by default
												   ->wrap(), // Wrap long notes
					  ])
			->filters([
						  // Add filters if needed, e.g., filter by items needing approval
					  ])
			->headerActions([
								Tables\Actions\CreateAction::make(), // Button to add a new item line
								Tables\Actions\AssociateAction::make()->preloadRecordSelect(), // Button to link an existing item (less common here)
							])
			->actions([
						  Tables\Actions\EditAction::make(), // Action to edit quantity/notes
						  Tables\Actions\DissociateAction::make(), // Action to remove item from request (doesn't delete item)
						  // Usually no DeleteAction here, Dissociate is safer
					  ])
			->bulkActions([
							  Tables\Actions\BulkActionGroup::make([
																	   Tables\Actions\DissociateBulkAction::make(),
																	   // Usually no DeleteBulkAction
																   ]),
						  ]);
	}
}