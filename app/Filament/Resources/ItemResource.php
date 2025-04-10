<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ItemResource extends Resource
{
	protected static ?string $model = Item::class;

	protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

	public static function form(Form $form): Form
	{
		return $form
			->schema([
						 Forms\Components\TextInput::make('name')
												   ->required()
												   ->maxLength(255),
						 Forms\Components\Textarea::make('description')
												  ->columnSpanFull(),
						 Forms\Components\TextInput::make('sku')
												   ->label('SKU')
												   ->required()
												   ->maxLength(255),
						 Forms\Components\Select::make('category_id')
												->relationship('category', 'name'),
						 Forms\Components\Select::make('unit')
												->options([
															  // Values from your CSV/Factory examples
															  'piece' => 'Piece', // Individual item
															  'bag' => 'Bag',     // Pre-packaged bag
															  'box' => 'Box',     // Box of items
															  'roll' => 'Roll',    // Roll (e.g., sponsor tokens)
															  'kit' => 'Kit',     // Represents a collection, might not be stocked itself
															  'pack' => 'Pack',    // Pack of items
															  'indv' => 'Individual', // Another way to say piece? Clarify if needed. From CSV.
															  'spec' => 'Special', // Special unit type from CSV
															  'case' => 'Case',    // Case (e.g., info sheet rack)
															  'pad' => 'Pad',     // Pad (e.g., receipts)
															  // Add any other standard units you use
														  ])
												->required()
												->searchable()
												->native(false) // Use Filament styling
												->default('piece'), // Set a sensible default
						 Forms\Components\TextInput::make('cost_per_unit')
												   ->numeric(),
						 Forms\Components\TextInput::make('value_per_unit')
												   ->numeric(),
						 Forms\Components\TextInput::make('reorder_threshold')
												   ->numeric(),
						 Forms\Components\FileUpload::make('image_path')
													->image(),
						 Forms\Components\Toggle::make('is_active')
												->required(),
						 Forms\Components\Textarea::make('notes')
												  ->columnSpanFull(),
					 ]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
						  Tables\Columns\TextColumn::make('name')
												   ->searchable(),
						  Tables\Columns\TextColumn::make('sku')
												   ->label('SKU')
												   ->searchable(),
						  Tables\Columns\TextColumn::make('category.name')
												   ->numeric()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('unit')
												   ->searchable(),
						  Tables\Columns\TextColumn::make('cost_per_unit')
												   ->numeric()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('value_per_unit')
												   ->numeric()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('reorder_threshold')
												   ->numeric()
												   ->sortable(),
						  Tables\Columns\ImageColumn::make('image_path'),
						  Tables\Columns\IconColumn::make('is_active')
												   ->boolean(),
						  Tables\Columns\TextColumn::make('created_at')
												   ->dateTime()
												   ->sortable()
												   ->toggleable(isToggledHiddenByDefault: TRUE),
						  Tables\Columns\TextColumn::make('updated_at')
												   ->dateTime()
												   ->sortable()
												   ->toggleable(isToggledHiddenByDefault: TRUE),
					  ])
			->filters([
						  //
					  ])
			->actions([
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
			RelationManagers\ItemInventoryStockRelationManager::class,
		];
	}

	public static function getPages(): array
	{
		return [
			'index'  => Pages\ListItems::route('/'),
			'create' => Pages\CreateItem::route('/create'),
			'edit'   => Pages\EditItem::route('/{record}/edit'),
		];
	}
}
