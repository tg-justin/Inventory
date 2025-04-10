<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
	protected static ?string $model = Category::class;

	protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

	public static function form(Form $form): Form
	{
		return $form
			->schema([
						 Forms\Components\TextInput::make('name')
												   ->required()
												   ->maxLength(255),
						 Forms\Components\Select::make('parent_id')
												->relationship('parent', 'name'),
						 Forms\Components\TextInput::make('sort_order')
												   ->required()
												   ->numeric()
												   ->default(0),
					 ]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
						  Tables\Columns\TextColumn::make('name')
												   ->searchable(),
						  Tables\Columns\TextColumn::make('parent.name')
												   ->numeric()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('sort_order')
												   ->numeric()
												   ->sortable(),
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
			RelationManagers\ItemsRelationManager::class,
		];
	}

	public static function getPages(): array
	{
		return [
			'index'  => Pages\ListCategories::route('/'),
			'create' => Pages\CreateCategory::route('/create'),
			'edit'   => Pages\EditCategory::route('/{record}/edit'),
		];
	}
}
