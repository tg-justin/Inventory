<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationResource\Pages;
use App\Filament\Resources\LocationResource\RelationManagers;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LocationResource extends Resource
{
	protected static ?string $model = Location::class;

	protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

	public static function form(Form $form): Form
	{
		return $form
			->schema([
						 Forms\Components\TextInput::make('name')
												   ->required()
												   ->maxLength(255),
						 Forms\Components\Toggle::make('is_primary_storage')
												->required(),
						 Forms\Components\Toggle::make('is_personal_cache')
												->required(),
						 Forms\Components\Textarea::make('address')
												  ->columnSpanFull(),
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
						  Tables\Columns\IconColumn::make('is_primary_storage')
												   ->boolean(),
						  Tables\Columns\IconColumn::make('is_personal_cache')
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
			RelationManagers\LocationInventoryStockRelationManager::class,
		];
	}

	public static function getPages(): array
	{
		return [
			'index'  => Pages\ListLocations::route('/'),
			'create' => Pages\CreateLocation::route('/create'),
			'edit'   => Pages\EditLocation::route('/{record}/edit'),
		];
	}
}
