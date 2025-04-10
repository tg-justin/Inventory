<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

// Import the Item model

class ItemsRelationManager extends RelationManager
{
	protected static string $relationship = 'items';

	public function form(Form $form): Form
	{
		// Form for creating/editing Items within this Category
		return $form
			->schema([
						 Forms\Components\TextInput::make('name')
												   ->required()
												   ->maxLength(255)
												   ->columnSpanFull(),
						 Forms\Components\TextInput::make('sku')
												   ->label('SKU')
												   ->required()
												   ->unique(Item::class, 'sku', ignoreRecord: TRUE) // Unique SKU across all items
												   ->maxLength(255),
						 Forms\Components\Select::make('unit')
												->options([
															  'piece' => 'Piece',
															  'bag'   => 'Bag',
															  'box'   => 'Box',
															  'roll'  => 'Roll',
															  'kit'   => 'Kit',
															  'pack'  => 'Pack',
															  'indv'  => 'Individual', // From spreadsheet
															  'case'  => 'Case', // From spreadsheet
															  'pad'   => 'Pad', // From spreadsheet
														  ])
												->required(),
						 Forms\Components\Textarea::make('description')
												  ->maxLength(65535)
												  ->columnSpanFull(),
						 Forms\Components\TextInput::make('cost_per_unit')
												   ->label('Cost per Unit')
												   ->prefix('$')
												   ->numeric()
												   ->step(0.01),
						 Forms\Components\TextInput::make('value_per_unit')
												   ->label('Value per Unit')
												   ->prefix('$')
												   ->numeric()
												   ->step(0.01),
						 Forms\Components\TextInput::make('reorder_threshold')
												   ->label('Reorder Threshold')
												   ->numeric()
												   ->integer(),
						 Forms\Components\Toggle::make('is_active')
												->label('Active')
												->default(TRUE),
						 Forms\Components\Textarea::make('notes')
												  ->maxLength(65535)
												  ->columnSpanFull(),
						 // category_id is handled automatically by the relationship
					 ]);
	}

	public function table(Table $table): Table
	{
		// Table listing Items belonging to this Category
		return $table
			->recordTitleAttribute('name')
			->columns([
						  Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
						  Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable()->sortable(),
						  Tables\Columns\TextColumn::make('unit')->sortable(),
						  Tables\Columns\TextColumn::make('cost_per_unit')->money('usd')->sortable(),
						  Tables\Columns\IconColumn::make('is_active')->label('Active')->boolean(),
						  Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: TRUE),
					  ])
			->filters([
						  //
					  ])
			->headerActions([
								Tables\Actions\CreateAction::make(), // Add new item TO this category
								// Tables\Actions\AssociateAction::make(), // Link existing item TO this category
							])
			->actions([
						  Tables\Actions\EditAction::make(),
						  Tables\Actions\DissociateAction::make(), // Remove item FROM this category (doesn't delete item)
						  // Tables\Actions\DeleteAction::make(), // If you want to allow deleting items from here
					  ])
			->bulkActions([
							  Tables\Actions\BulkActionGroup::make([
																	   Tables\Actions\DissociateBulkAction::make(),
																	   // Tables\Actions\DeleteBulkAction::make(),
																   ]),
						  ]);
	}
}