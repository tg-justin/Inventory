<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Filament\Resources\LocationResource;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;

// Import Model

// Import Location

// RENAME THE CLASS
class ItemInventoryStockRelationManager extends RelationManager
{
	protected static string $relationship = 'inventoryStock';

	protected static ?string $title = 'Stock Locations for this Item';

	public function form(Form $form): Form
	{
		// Form for adding/editing stock OF this Item
		return $form
			->schema([
						 Forms\Components\Select::make('location_id')
												->label('Location')
												->options(Location::query()->pluck('name', 'id')) // List all locations
												->searchable()
												->required()
							 // Prevent adding stock for this item at a location where it already exists via this form
												->unique(
								 table:        'inventory_stock',
								 column:       'location_id',
								 ignoreRecord: TRUE,
								 modifyRuleUsing: function (Unique $rule, RelationManager $livewire) {
									 return $rule->where('item_id', $livewire->ownerRecord->id);
								 }
							 ),
						 Forms\Components\TextInput::make('quantity')
												   ->numeric()
												   ->required()
												   ->minValue(0)
												   ->integer(),
						 // item_id is handled automatically
					 ]);
	}

	public function table(Table $table): Table
	{
		// Table listing locations WHERE this Item has stock
		return $table
			->recordTitleAttribute('location.name') // Show Location Name
			->columns([
						  Tables\Columns\TextColumn::make('location.name')
												   ->label('Location Name')
												   ->searchable()
												   ->sortable()
							  // Optional: Link to the Location resource
												   ->url(fn(Model $record): string => LocationResource::getUrl('edit', ['record' => $record->location_id])),
						  Tables\Columns\TextColumn::make('quantity')
												   ->sortable()
						  // ->inlineEdit() // Requires custom logic later
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
								Tables\Actions\CreateAction::make() // Add stock of this item to a NEW location
														   ->label('Add Stock to Location'),
							])
			->actions([
						  Tables\Actions\EditAction::make(), // Edit quantity
						  // Maybe add a custom "Adjust Stock" action later
					  ])
			->bulkActions([
							  // Tables\Actions\BulkActionGroup::make([
							  //     Tables\Actions\DeleteBulkAction::make(),
							  // ]),
						  ]);
	}
}