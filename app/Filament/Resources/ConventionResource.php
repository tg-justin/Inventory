<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConventionResource\Pages;
use App\Filament\Resources\ConventionResource\RelationManagers;
use App\Models\Convention;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConventionResource extends Resource
{
	protected static ?string $model = Convention::class;

	protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

	public static function form(Form $form): Form
	{
		return $form
			->schema([
						 Forms\Components\TextInput::make('name')
												   ->required()
												   ->maxLength(255),
						 Forms\Components\DatePicker::make('start_date')
													->required(),
						 Forms\Components\DatePicker::make('end_date')
													->required(),
						 Forms\Components\TextInput::make('city')
												   ->maxLength(255),
						 Forms\Components\TextInput::make('state')
												   ->maxLength(255),
						 Forms\Components\TextInput::make('venue')
												   ->maxLength(255),
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
						  Tables\Columns\TextColumn::make('start_date')
												   ->date()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('end_date')
												   ->date()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('city')
												   ->searchable(),
						  Tables\Columns\TextColumn::make('state')
												   ->searchable(),
						  Tables\Columns\TextColumn::make('venue')
												   ->searchable(),
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
			RelationManagers\ConventionRequestsRelationManager::class,
		];
	}

	public static function getPages(): array
	{
		return [
			'index'  => Pages\ListConventions::route('/'),
			'create' => Pages\CreateConvention::route('/create'),
			'edit'   => Pages\EditConvention::route('/{record}/edit'),
		];
	}
}
