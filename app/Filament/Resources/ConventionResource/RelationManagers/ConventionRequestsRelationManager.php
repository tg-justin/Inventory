<?php

namespace App\Filament\Resources\ConventionResource\RelationManagers;

use App\Enums\ConventionRequestStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

// Import User model

class ConventionRequestsRelationManager extends RelationManager
{
	protected static string $relationship = 'conventionRequests';

	protected static ?string $title = 'Requests for this Convention';

	public function form(Form $form): Form
	{
		// Form for creating/editing Requests FOR this Convention
		return $form
			->schema([
						 Forms\Components\Select::make('requester_id')
												->label('Requester')
												->relationship('requester', 'name') // Assumes 'requester' relationship on ConventionRequest model
												->searchable()
												->preload() // Good if user list isn't huge
												->required(),
						 Forms\Components\DatePicker::make('request_date')
													->required()
													->default(now()),
						 Forms\Components\DatePicker::make('needed_by_date')
													->required(),
						 Forms\Components\Select::make('status')
													->label('Status')
													->options(ConventionRequestStatus::options()) // Use the helper method
													->required()
													->searchable()
													->native(false)
													->default('draft'),
						 Forms\Components\Textarea::make('shipping_address')
												  ->label('Shipping Address')
												  ->rows(3)
												  ->columnSpanFull(),
						 Forms\Components\TextInput::make('shipping_tracking_number')
												   ->label('Tracking Number')
												   ->columnSpanFull(),
						 Forms\Components\Textarea::make('notes')
												  ->label('Requester Notes')
												  ->rows(3)
												  ->columnSpanFull(),
						 Forms\Components\Textarea::make('admin_notes')
												  ->label('Admin Notes (Internal)')
												  ->rows(3)
												  ->columnSpanFull(),
						 // convention_id is handled automatically
					 ]);
	}

	public function table(Table $table): Table
	{
		// Table listing Requests FOR this Convention
		return $table
			->recordTitleAttribute('id') // Or maybe requester name?
			->columns([
						  Tables\Columns\TextColumn::make('requester.name')->searchable()->sortable(),
						  Tables\Columns\TextColumn::make('status')
														->badge() // Automatically uses HasColor and HasLabel from Enum
														->sortable()
														->searchable(),
						  Tables\Columns\TextColumn::make('request_date')->date()->sortable(),
						  Tables\Columns\TextColumn::make('needed_by_date')->date()->sortable(),
						  Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: TRUE),
					  ])
			->filters([
						  // Filter by status?
						  Tables\Filters\SelectFilter::make('status')
													 ->options([/* same options as form select */]),
					  ])
			->headerActions([
								Tables\Actions\CreateAction::make(), // Add new request FOR this convention
							])
			->actions([
						  Tables\Actions\ViewAction::make(), // Link to view the full request (ConventionRequestResource page)
						  Tables\Actions\EditAction::make(),
						  Tables\Actions\DeleteAction::make(),
					  ])
			->bulkActions([
							  Tables\Actions\BulkActionGroup::make([
																	   Tables\Actions\DeleteBulkAction::make(),
																   ]),
						  ]);
	}
}