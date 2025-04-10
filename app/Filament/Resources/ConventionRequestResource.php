<?php

namespace App\Filament\Resources;

use App\Enums\ConventionRequestStatus;
use App\Filament\Resources\ConventionRequestResource\Pages;
use App\Filament\Resources\ConventionRequestResource\RelationManagers;
use App\Filament\Resources\ConventionRequestResource\RelationManagers\ConventionReconciliationsRelationManager;
use App\Filament\Resources\ConventionRequestResource\RelationManagers\InventoryMovementsRelationManager;
use App\Models\ConventionRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ConventionRequestResource extends Resource
{
	protected static ?string $model = ConventionRequest::class;

	protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

	public static function form(Form $form): Form
	{
		return $form
			->schema([
						 Forms\Components\Select::make('convention_id')
												->relationship('convention', 'name')
												->required(),
						 Forms\Components\Select::make('requester_id')
												->relationship('requester', 'name'),
						 Forms\Components\DatePicker::make('request_date')
													->required(),
						 Forms\Components\DatePicker::make('needed_by_date')
													->required(),
						 Forms\Components\Select::make('status')
												->label('Status')
												->options(ConventionRequestStatus::options()) // Use the helper method
												->required()
												->searchable()
												->native(false), // Use Filament's styled select
						 Forms\Components\Textarea::make('shipping_address')
												  ->columnSpanFull(),
						 Forms\Components\TextInput::make('shipping_tracking_number')
												   ->maxLength(255),
						 Forms\Components\Textarea::make('notes')
												  ->columnSpanFull(),
						 Forms\Components\Textarea::make('admin_notes')
												  ->columnSpanFull(),
					 ]);
	}

	public static function table(Table $table): Table
	{
		return $table
			->columns([
						  Tables\Columns\TextColumn::make('convention.name')
												   ->numeric()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('requester.name')
												   ->numeric()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('request_date')
												   ->date()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('needed_by_date')
												   ->date()
												   ->sortable(),
						  Tables\Columns\TextColumn::make('status')
												   ->badge() // Automatically uses HasColor and HasLabel from Enum
												   ->sortable()
												   ->searchable(),
						  Tables\Columns\TextColumn::make('shipping_tracking_number')
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
			RelationManagers\ConventionRequestItemsRelationManager::class,
			InventoryMovementsRelationManager::class,     // For auditing stock changes related to this request
			ConventionReconciliationsRelationManager::class, // For post-convention data entry
		];
	}

	public static function getPages(): array
	{
		return [
			'index'  => Pages\ListConventionRequests::route('/'),
			'create' => Pages\CreateConventionRequest::route('/create'),
			'edit'   => Pages\EditConventionRequest::route('/{record}/edit'),
		];
	}
}
