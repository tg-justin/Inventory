<?php

namespace App\Filament\Resources\ConventionRequestResource\Pages;

use App\Enums\ConventionRequestStatus; // <-- Make sure Enum is imported
use App\Filament\Resources\ConventionRequestResource;
use App\Models\ConventionRequest; // <-- Import main model
use App\Models\ConventionRequestItem;
use Filament\Actions;
use Filament\Forms\Components\Hidden;     // Import Hidden
use Filament\Forms\Components\Placeholder; // Import Placeholder
use Filament\Forms\Components\Repeater;   // Import Repeater
use Filament\Forms\Components\TextInput;  // Import TextInput
use Filament\Notifications\Notification; // Import Notification
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditConventionRequest extends EditRecord
{
	protected static string $resource = ConventionRequestResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\DeleteAction::make(),

			// --- REFACTORED APPROVE ACTION ---
			Actions\Action::make('approveRequest')
						  ->label('Review & Approve') // Changed label slightly
						  ->color('success')
						  ->icon('heroicon-o-check-circle')
						  ->visible(fn ($record) => $record->status === ConventionRequestStatus::Submitted) // Use Enum
				// --- Modal Form for Approval ---
						  ->form(function ($record) { // Use closure to access record data
					return [
						Repeater::make('approved_items')
								->label('Approve Items')
							// Load items related to the current request
								->relationship('conventionRequestItems')
							// Configure Repeater appearance/behavior
								->columns(3)
								->addable(false)->deletable(false)->reorderable(false)
								->required()
								->schema([
											 // Store the ID of the ConventionRequestItem
											 Hidden::make('id'),
											 // Display Item Name (Readonly)
											 Placeholder::make('item_name')
														->label('Item')
														->content(fn ($record): ?string => $record?->item?->name ?? 'N/A')
														->columnSpan(2),
											 // Display Quantity Requested (Readonly)
											 Placeholder::make('quantity_requested_info')
														->label('Requested')
														->content(fn ($record): ?string => $record?->quantity_requested ?? '0'),
											 // Input for Approved Quantity
											 TextInput::make('quantity_approved')
													  ->label('Quantity Approved')
													  ->numeric()
													  ->required()
													  ->minValue(0)
												 // Optional: Prevent approving more than requested
												 // ->maxValue(fn ($record) => $record?->quantity_requested ?? 0)
												 // Default approved quantity to requested quantity
													  ->default(fn ($record): ?int => $record?->quantity_requested ?? 0)
													  ->columnSpan(3), // Span full width within repeater row
										 ])
							// IMPORTANT: Disable automatic relationship saving for the repeater
								->saveRelationshipsUsing(null)
								->dehydrated(false), // Don't save the repeater data structure itself
					];
				})
				// --- Action Logic ---
						  ->action(function (ConventionRequest $record, array $data) {
					// $data will contain ['approved_items' => [... repeater data ...]]
					try {
						DB::transaction(function () use ($record, $data) {
							$totalApproved = 0;
							foreach ($data['approved_items'] as $itemData) {
								$requestItem = ConventionRequestItem::find($itemData['id']);
								if ($requestItem) {
									$approvedQty = (int) $itemData['quantity_approved'];
									$requestItem->update([
															 'quantity_approved' => $approvedQty
														 ]);
									$totalApproved += $approvedQty;
								}
							}

							// Update the main request status only if something was actually approved
							// (or decide your logic: maybe approve even if all are 0?)
							if ($totalApproved > 0) {
								$record->update(['status' => ConventionRequestStatus::Approved]);
							} else {
								// Optional: Handle case where everything is denied (0 approved)
								// Maybe set status to Rejected or keep as Submitted?
								// For now, let's just approve it as an empty request
								$record->update(['status' => ConventionRequestStatus::Approved]);
								// Alternatively:
								// $record->update(['status' => ConventionRequestStatus::Rejected]);
								// Notification::make()->title('Request Rejected')->body('All item quantities were set to zero.')->warning()->send();
								// return; // Stop further processing if rejected
							}
						});

						Notification::make()
									->title('Request Quantities Approved')
									->success()
									->send();

					} catch (\Exception $e) {
						Notification::make()
									->title('Approval Failed')
									->body('An error occurred: ' . $e->getMessage())
									->danger()
									->send();
					}
				})
						  ->modalWidth('xl') // Make modal wider
						  ->modalSubmitActionLabel('Confirm Approved Quantities'),

			Actions\Action::make('shipRequest')
						  ->label('Prepare Shipment')
						  ->color('info')
						  ->icon('heroicon-o-truck')
						  ->visible(fn ($record) => $record->status === ConventionRequestStatus::Approved) // Use Enum
				// --- Modal Form for Shipping Details ---
						  ->form(function ($record) { // Use closure to access record data
					return [
						Forms\Components\Select::make('from_location_id') // Use Forms\Components namespace
											   ->label('Ship From Location')
											   ->options(Location::pluck('name', 'id')) // Import Location model
							// TODO: Filter locations with stock
											   ->searchable()
											   ->required(),
						Forms\Components\TextInput::make('shipping_tracking_number')
												  ->label('Tracking Number(s)') // Allow multiple
												  ->nullable(),
						// --- Repeater for Items to Ship ---
						Repeater::make('items_to_ship')
								->label('Items to Ship')
							// Custom relationship query to ONLY get approved items
								->relationship(
								'conventionRequestItems', // Relationship name
								fn (Builder $query) => $query->where('quantity_approved', '>', 0) // Filter directly
							)
								->columns(3) // Adjust columns as needed
								->addable(false)->deletable(false)->reorderable(false)
								->required()
								->schema([
											 Hidden::make('id'), // ConventionRequestItem ID
											 Placeholder::make('item_name')
														->label('Item')
														->content(fn ($record): ?string => $record?->item?->name ?? 'N/A')
														->columnSpan(2),
											 Placeholder::make('quantity_approved_info')
														->label('Approved')
														->content(fn ($record): ?string => $record?->quantity_approved ?? '0'),
											 TextInput::make('quantity_shipped')
													  ->label('Quantity to Ship')
													  ->numeric()
													  ->required()
													  ->minValue(0)
												 // Can't ship more than approved
													  ->maxValue(fn ($record): ?int => $record?->quantity_approved ?? 0)
												 // TODO: Add validation against actual stock in from_location_id (complex)
													  ->default(fn ($record): ?int => $record?->quantity_approved ?? 0) // Default to approved
													  ->columnSpan(3),
										 ])
								->saveRelationshipsUsing(null)
								->dehydrated(false),
					];
				})
						  ->action(function (ConventionRequest $record, array $data) {
					$fromLocationId = $data['from_location_id'];
					$tracking = $data['shipping_tracking_number'];

					// TODO: Optional - Add pre-check for stock levels here before transaction

					try {
						DB::transaction(function() use ($record, $fromLocationId, $tracking, $data) {
							$totalShipped = 0;
							foreach ($data['items_to_ship'] as $itemData) {
								$requestItem = ConventionRequestItem::find($itemData['id']);
								if ($requestItem) {
									$quantityToShip = (int) $itemData['quantity_shipped'];

									if ($quantityToShip > 0) {
										// 1. Update the ConventionRequestItem
										$requestItem->update(['quantity_shipped' => $quantityToShip]);

										// 2. Create Inventory Movement Record
										InventoryMovement::create([ // Import InventoryMovement model
																	'item_id' => $requestItem->item_id,
																	'quantity' => -$quantityToShip,
																	'from_location_id' => $fromLocationId,
																	'to_location_id' => null,
																	'user_id' => auth()->id(),
																	'movement_type' => 'convention_shipment',
																	'related_entity_type' => ConventionRequest::class,
																	'related_entity_id' => $record->id,
																	'notes' => "Shipped for request #{$record->id} ({$record->convention->name})",
																  ]);
										$totalShipped += $quantityToShip;
										// NOTE: Observer/Listener updates InventoryStock
									} else {
										// If quantity shipped is 0, ensure it's saved on the item
										$requestItem->update(['quantity_shipped' => 0]);
									}
								}
							}

							// 3. Update the main request status and tracking
							// Only mark as shipped if something was actually shipped? Or always if action taken? Decision needed.
							// Let's mark as shipped if the action was completed, even if quantity was 0 for all.
							$record->update([
												'status' => ConventionRequestStatus::Shipped, // Use Enum
												'shipping_tracking_number' => $tracking,
											]);
						});

						Notification::make()
									->title('Request Marked as Shipped')
									->body('Inventory movements have been recorded for shipped items.')
									->success()
									->send();

					} catch (\Exception $e) {
						report($e);
						Notification::make()
									->title('Shipping Failed')
									->body('An error occurred: ' . $e->getMessage())
									->danger()
									->send();
					}
				})
						  ->modalWidth('xl')
						  ->modalSubmitActionLabel('Confirm Shipment & Record Movements')
		]; // End of getHeaderActions array
	}
}