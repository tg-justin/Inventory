<?php

namespace App\Filament\Resources\LocationResource\RelationManagers;

use App\Filament\Resources\ItemResource;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

// Import Model

// Import Item

// RENAME THE CLASS
class LocationInventoryStockRelationManager extends RelationManager
{
	// Relationship name on the Location model
	protected static string $relationship = 'inventoryStock';

	// Set a Navigation Title
	protected static ?string $title = 'Stock at this Location';

	public function form(Form $form): Form
	{
		// Form for adding/editing stock FOR this Location
		return $form
			->schema([
						 Forms\Components\Select::make('item_id')
												->label('Item')
												->options(Item::query()->pluck('name', 'id')) // List all items
												->searchable()
												->required()
							 // Prevent adding stock for an item already present at this location via this form
							 // Use Edit action for existing stock.
												->unique( // Unique combination for this location
								 table:        'inventory_stock',
								 column:       'item_id',
								 ignoreRecord: TRUE, // Important for edit
								 modifyRuleUsing: function (Unique $rule, RelationManager $livewire) {
									 return $rule->where('location_id', $livewire->ownerRecord->id);
								 }
							 ),
						 Forms\Components\TextInput::make('quantity')
												   ->numeric()
												   ->required()
												   ->minValue(0) // Prevent negative stock via form
												   ->integer(),
						 // location_id is handled automatically
					 ]);
	}

	public function table(Table $table): Table
	{
		// Table listing stock AT this Location
		return $table
			->recordTitleAttribute('item.name') // Show Item Name
			->columns([
						  Tables\Columns\TextColumn::make('item.name')
												   ->label('Item Name')
												   ->searchable()
												   ->sortable()
							  // Optional: Link to the Item resource
												   ->url(fn(Model $record): string => ItemResource::getUrl('edit', ['record' => $record->item_id])),
						  Tables\Columns\TextColumn::make('item.sku')->label('SKU')->searchable(),
						  Tables\Columns\TextColumn::make('quantity')
												   ->sortable()
						  // ->inlineEdit() // Enable inline editing - REQUIRES custom update logic later
						  ,
						  Tables\Columns\TextColumn::make('updated_at')
												   ->label('Last Updated')
												   ->dateTime()
												   ->sortable()
												   ->since(),
					  ])
			->filters([
						  //
					  ])
			->headerActions([
								Tables\Actions\CreateAction::make() // Add stock for a NEW item at this location
														   ->label('Add Item Stock'),
							])
			->actions([
						  Tables\Actions\EditAction::make(), // Edit quantity (via modal)
						  // Maybe add a custom "Adjust Stock" action later that creates InventoryMovement
					  ])
			->bulkActions([
							  // Bulk actions might not be safe/useful here without careful thought
							  // Tables\Actions\BulkActionGroup::make([
							  //     Tables\Actions\DeleteBulkAction::make(),
							  // ]),
						  ]);
	}
}