<?php

namespace App\Filament\Resources\ConventionRequestResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InventoryMovementsRelationManager extends RelationManager
{
	// Uses the polymorphic relationship defined in ConventionRequest model
	protected static string $relationship = 'inventoryMovements';

	// Use created_at or movement_type maybe? No single best title.
	protected static ?string $recordTitleAttribute = 'movement_type';

	// This relation manager should be VIEW ONLY in this context.
	// We don't create/edit movements directly here related to a request.
	// They are created by the "Ship" action or the Reconciliation process.

	public function form(Form $form): Form
	{
		// No form needed for viewing/editing in this context
		return $form->schema([]);
	}

	public function table(Table $table): Table
	{
		return $table
			// ->recordTitleAttribute('movement_type')
			->defaultSort('created_at', 'desc')
			->columns([Tables\Columns\TextColumn::make('created_at')->label('Timestamp')->dateTime('Y-m-d H:i:s') // Format as needed
												->sortable(),
					   Tables\Columns\TextColumn::make('item.name')->label('Item')->searchable()->sortable(), Tables\Columns\TextColumn::make('quantity')->label('Qty Change')->numeric()
						   // Optionally format to show +/-
						   ->formatStateUsing(fn(int $state): string => ($state > 0 ? '+' : '') . $state)->sortable(), Tables\Columns\TextColumn::make('fromLocation.name')->label('From Location')->sortable()->placeholder('Source'), // Placeholder for Purchase/Adjustment
					   Tables\Columns\TextColumn::make('toLocation.name')->label('To Location')->sortable()->placeholder('Usage/Loss'), // Placeholder for Usage/Loss
					   Tables\Columns\TextColumn::make('movement_type')->label('Type')->badge() // Display as a badge for clarity
												->color(fn(string $state): string => match ($state) {
						   'purchase' => 'success',
						   'transfer' => 'info',
						   'adjustment' => 'warning',
						   'convention_shipment' => 'primary',
						   'convention_usage' => 'danger',
						   'convention_return' => 'success',
						   default => 'gray',
					   })->searchable()->sortable(), Tables\Columns\TextColumn::make('user.name')->label('User')->sortable()->placeholder('System/Unknown')->toggleable(isToggledHiddenByDefault: TRUE), Tables\Columns\TextColumn::make('notes')->label('Notes')->wrap()->toggleable(isToggledHiddenByDefault: TRUE),])->filters([// Maybe filter by movement_type
																																																																																  ])->headerActions([// No header actions - read only
																																																																																					])->actions([// No edit/delete actions - read only
																																																																																								 // Tables\Actions\ViewAction::make(), // Optionally add view if needed
																																																																																								])->bulkActions([// No bulk actions - read only
																																																																																												]);
	}

	// Disable create/edit/delete globally for this manager in this context
	public function canCreate(): bool
	{
		return FALSE;
	}

	public function canEdit(Model $record): bool
	{
		return FALSE;
	}

	public function canDelete(Model $record): bool
	{
		return FALSE;
	}

	public function canDeleteAny(): bool
	{
		return FALSE;
	}

	public function canForceDelete(Model $record): bool
	{
		return FALSE;
	}

	public function canForceDeleteAny(): bool
	{
		return FALSE;
	}

	public function canReplicate(Model $record): bool
	{
		return FALSE;
	}

	public function canAssociate(): bool
	{
		return FALSE;
	}

	public function canDissociate(Model $record): bool
	{
		return FALSE;
	}

	public function canDissociateAny(): bool
	{
		return FALSE;
	}

}