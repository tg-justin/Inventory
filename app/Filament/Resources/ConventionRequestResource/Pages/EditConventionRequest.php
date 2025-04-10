<?php

namespace App\Filament\Resources\ConventionRequestResource\Pages;

use App\Enums\ConventionRequestStatus;
use App\Filament\Resources\ConventionRequestResource;
use App\Models\ConventionRequest;
use App\Models\ConventionRequestItem;
use App\Models\InventoryMovement;
use App\Models\Item;
use App\Models\Location;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditConventionRequest extends EditRecord
{
	protected static string $resource = ConventionRequestResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\DeleteAction::make(),

			// --- APPROVE ACTION with Explicit Data Loading via fillForm() and Debugging ---
			Actions\Action::make('approveRequest')
						  ->label('Review & Approve')
						  ->color('success')
						  ->icon('heroicon-o-check-circle')
						  ->visible(fn ($record) => $record->status === ConventionRequestStatus::Submitted)
						  ->fillForm(function (ConventionRequest $record): array {
							$itemsData = $record->conventionRequestItems()
										->with('item:id,name') // Eager load necessary item details
										->get()
										->map(fn (ConventionRequestItem $reqItem) => [
											'id' => $reqItem->id, // convention_request_item ID
											'item_name' => $reqItem->item?->name ?? 'N/A',
											'quantity_requested' => $reqItem->quantity_requested,
											// Default approved to existing approved or requested qty
											'quantity_approved' => $reqItem->quantity_approved ?? $reqItem->quantity_requested ?? 0,
										])
										->toArray();

					// Return the structure matching the form, including the repeater name
					return [
						'approved_items' => $itemsData,
					];
				})

				// --- Modal Form Definition ---
					  ->form([ // Note: $record is not directly available here, use fillForm() above
							   Repeater::make('approved_items')
									   ->label('Approve Items')
									   ->columns(3)
									   ->addable(false)->deletable(false)->reorderable(false)
									   ->required()
									   ->schema([
												// Hidden field for the ConventionRequestItem ID
												Hidden::make('id'), // Populated by fillForm

												// Display Item Name (from pre-filled data)
												Placeholder::make('item_name') // Matches key from fillForm map
														   ->label('Item')
														   ->content(function ($state): string {
														return $state;
													})
														->columnSpan(2),

												// Display Quantity Requested (from pre-filled data)
												Placeholder::make('quantity_requested')
														   ->label('Requested')
														   ->content(function ($state) {
															   return $state;
														   }),

												// Input for Approved Quantity
												TextInput::make('quantity_approved') // Matches key from fillForm map
														 ->label('Quantity Approved')
														 ->numeric()
														 ->required()
														 ->minValue(0)
														 ->default(function ($state): int {
															return $state;
														 })
														 ->columnSpan(3),
												])
							 ])
					// --- Action Logic (Processes the submitted array data) ---
						  ->action(function (ConventionRequest $record, array $data) {

					if (!isset($data['approved_items'])) {
						Notification::make()->title('Approval Error')->body('No item data received.')->danger()->send();
						return;
					}

					try {
						DB::transaction(function () use ($record, $data) {
							$totalApproved = 0;
							foreach ($data['approved_items'] as $itemData) {
								if (!isset($itemData['id']) || !isset($itemData['quantity_approved'])) {
									Log::warning('Approve Action: Skipping item due to missing data.', ['item_data' => $itemData]);
									continue;
								}
								$requestItem = ConventionRequestItem::find($itemData['id']);
								if ($requestItem) {
									$approvedQty = max(0, (int) $itemData['quantity_approved']);
									$requestItem->update(['quantity_approved' => $approvedQty]);
									$totalApproved += $approvedQty;
								} else { Log::warning(/* ... */); }
							}
							$record->update(['status' => ConventionRequestStatus::Approved]);
						});
						Notification::make()->title('Request Quantities Approved')->success()->send();
					} catch (\Exception $e) {
						Log::error('Approve Action Failed:', ['error' => $e->getMessage()]); // Simpler log
						Notification::make()->title('Approval Failed')->body('An error occurred. Please check logs.')->danger()->send();
					}
				})
						  ->modalWidth('xl')
						  ->modalSubmitActionLabel('Confirm Approved Quantities'),


			// --- SHIP ACTION (Apply similar fillForm approach) ---
			Actions\Action::make('shipRequest')
						  ->label('Prepare Shipment')
						  ->color('info')
						  ->icon('heroicon-o-truck')
						  ->visible(fn ($record) => $record->status === ConventionRequestStatus::Approved)
						  ->fillForm(function (ConventionRequest $record): array {
							  // Pre-load data for items that are approved
							  $itemsData = $record->conventionRequestItems()
												  ->where('quantity_approved', '>', 0) // Only load approved items
												  ->with('item:id,name')
												  ->get()
												  ->map(fn (ConventionRequestItem $reqItem) => [
													  'id' => $reqItem->id,
													  'item_name' => $reqItem->item?->name ?? 'N/A',
													  'quantity_approved' => $reqItem->quantity_approved,
													  // Default shipped to current shipped OR approved
													  'quantity_shipped' => $reqItem->quantity_shipped ?? $reqItem->quantity_approved ?? 0,
												  ])
												  ->toArray();
							  return [
								  'items_to_ship' => $itemsData,
								  // Can prefill other fields too if needed, e.g., default location
								  // 'from_location_id' => ...
							  ];
						  })
						  ->form([
									 Select::make('from_location_id')
										   ->label('Ship From Location')
										   ->options(Location::pluck('name', 'id'))
										   ->searchable()
										   ->required(),
									 TextInput::make('shipping_tracking_number')
											  ->label('Tracking Number(s)')
											  ->nullable(),
									 Repeater::make('items_to_ship')
											 ->label('Items to Ship')
										 // REMOVE ->relationship()
											 ->columns(3)
											 ->addable(false)->deletable(false)->reorderable(false)
											 ->required()
											 ->schema([
														  Hidden::make('id'),
														  Placeholder::make('item_name')
																	 ->label('Item')
																	 ->content(function ($state) {
																			 return $state;
																	 })
																	 ->columnSpan(2),
														  Placeholder::make('quantity_approved') // Changed key to match fillForm
																	 ->label('Approved')
																	 ->content(function ($state) {
																			 return $state;
																	 }),

														  TextInput::make('quantity_shipped') // Matches key from fillForm
																   ->label('Quantity to Ship')
																   ->numeric()->required()->minValue(0)
																   ->maxValue(function ($state) {
																	   return $state;
																   })
																   ->default(function ($state) {
																		   return $state;
																   })
																   ->columnSpan(3),
													  ]),
								 ])
						  ->action(function (ConventionRequest $record, array $data) {
							  // Action logic remains largely the same, processing $data['items_to_ship']
							  Log::info('Ship Action Data Received:', ['data' => $data]);
							  if (!isset($data['from_location_id']) || !isset($data['items_to_ship'])) { /* Error */ return; }
							  $fromLocationId = $data['from_location_id'];
							  $tracking = $data['shipping_tracking_number'];
							  try {
								  DB::transaction(function() use ($record, $fromLocationId, $tracking, $data) {
									  foreach ($data['items_to_ship'] as $itemData) {
										  if (!isset($itemData['id']) || !isset($itemData['quantity_shipped'])) { continue; }
										  $requestItem = ConventionRequestItem::find($itemData['id']);
										  if ($requestItem) {
											  $quantityToShip = max(0, (int) $itemData['quantity_shipped']);
											  $requestItem->update(['quantity_shipped' => $quantityToShip]);
											  if ($quantityToShip > 0) {
												  // Ensure InventoryMovement data is correctly structured
												  InventoryMovement::create([
																				'item_id'             => $requestItem->item_id,
																				'quantity'            => -$quantityToShip,
																				'from_location_id'    => $fromLocationId,
																				'to_location_id'      => null,
																				'user_id'             => auth()->id(),
																				'movement_type'       => 'convention_shipment',
																				'related_entity_type' => ConventionRequest::class, // Make sure this is correct
																				'related_entity_id'   => $record->id,
																				'notes'               => "Shipped for request #{$record->id} ({$record->convention->name})",
																			]);
											  }
										  } else { Log::warning('Ship Action: Could not find CRI', ['id' => $itemData['id']]); }
									  }
									  $record->update([
														  'status' => ConventionRequestStatus::Shipped,
														  'shipping_tracking_number' => $tracking,
													  ]);
								  });
								  Notification::make()->title('Request Marked as Shipped')->success()->send();
							  } catch (\Exception $e) {
								  Log::error('Ship Action Failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
								  Notification::make()->title('Shipping Failed')->body('An error occurred. Please check logs.')->danger()->send();
							  }
						  })
						  ->modalWidth('xl')
						  ->modalSubmitActionLabel('Confirm Shipment & Record Movements'),

			// --- NEW RECONCILE ACTION ---
			Actions\Action::make('reconcileItems')
						  ->label('Reconcile Items')
						  ->color('warning')
						  ->icon('heroicon-o-archive-box-x-mark') // Or another suitable icon
				// Show when shipped (or received, if you add that state)
						  ->visible(fn ($record) => $record->status === ConventionRequestStatus::Shipped)

				// --- Pre-fill form with shipped items ---
						  ->fillForm(function (ConventionRequest $record): array {
					$itemsData = $record->conventionRequestItems()
										->where('quantity_shipped', '>', 0) // Only items that were shipped
										->with(['item:id,name', 'conventionReconciliation']) // Eager load existing reconciliation data too
										->get()
										->map(function (ConventionRequestItem $reqItem) {
											$recon = $reqItem->conventionReconciliation; // Get related recon record if exists
											return [
												'request_item_id' => $reqItem->id, // Keep track of the request item
												'item_name' => $reqItem->item?->name ?? 'N/A',
												'quantity_shipped' => $reqItem->quantity_shipped,
												// Populate existing reconciliation data or defaults
												'quantity_used' => $recon?->quantity_used ?? 0,
												'quantity_returned_to_storage' => $recon?->quantity_returned_to_storage ?? 0,
												'return_location_id' => $recon?->return_location_id ?? null,
												'quantity_kept_by_requester' => $recon?->quantity_kept_by_requester ?? 0,
												'requester_kept_location_id' => $recon?->requester_kept_location_id ?? null,
												'quantity_lost_damaged' => $recon?->quantity_lost_damaged ?? 0,
											];
										})
										->toArray();
					// Default other form fields
					return [
						'reconciliation_date' => now()->format('Y-m-d'),
						'reconciler_id' => auth()->id(),
						'reconciled_items' => $itemsData,
					];
				})

				// --- Modal Form Definition ---
						  ->form([
									 // Top level fields for the reconciliation event
									 DatePicker::make('reconciliation_date')
											   ->label('Reconciliation Date')
											   ->required()
											   ->native(false), // Use Filament's picker
									 Select::make('reconciler_id')
										   ->label('Reconciled By')
										   ->relationship('requester', 'name') // Or a specific 'reconcilers' relationship/query
										   ->default(auth()->id())
										   ->searchable()
										   ->required()
										   ->native(false),

									 // --- Repeater for each shipped item ---
									 Repeater::make('reconciled_items')
											 ->label('Items Reconciliation')
											 ->columns(6) // Use more columns for layout
											 ->addable(false)->deletable(false)->reorderable(false)
											 ->required()
											 ->schema([
														  // Store request_item_id to link back if needed, although repeater key might be enough
														  Hidden::make('request_item_id'),

														  // --- Display Only ---
														  Placeholder::make('item_name')
																	 ->label('Item')
																	 ->content(fn (array $state): string => $state['item_name'] ?? 'N/A')
																	 ->columnSpan(6), // Full width row
														  Placeholder::make('quantity_shipped')
																	 ->label('Qty Shipped')
																	 ->content(fn (array $state): string => $state['quantity_shipped'] ?? '0')
																	 ->columnSpan(1),

														  // --- Inputs ---
														  TextInput::make('quantity_used')
																   ->label('Used')
																   ->numeric()->required()->minValue(0)
																   ->columnSpan(1),
														  TextInput::make('quantity_returned_to_storage')
																   ->label('Returned')
																   ->numeric()->required()->minValue(0)
																   ->reactive() // Needed for conditional field below
																   ->columnSpan(1),
														  TextInput::make('quantity_kept_by_requester')
																   ->label('Kept')
																   ->numeric()->required()->minValue(0)
																   ->reactive() // Needed for conditional field below
																   ->columnSpan(1),
														  TextInput::make('quantity_lost_damaged')
																   ->label('Lost/Dmg')
																   ->numeric()->required()->minValue(0)
																   ->columnSpan(1),

														  // Spacer or adjust spans if needed
														  Placeholder::make('spacer')->columnSpan(1),


														  // --- Conditional Location Selects (on next row) ---
														  Select::make('return_location_id')
																->label('Return Location')
																->options(Location::where('is_personal_cache', false)->pluck('name', 'id')) // Main storage options
																->searchable()
																->requiredIf('quantity_returned_to_storage', '>', 0) // Use RequiredIf validation rule
																->visible(fn ($get) => $get('quantity_returned_to_storage') > 0) // $get relative to repeater item state
																->columnSpan(3), // Span half width
														  Select::make('requester_kept_location_id')
																->label('Kept Location (Cache)')
																->options(Location::where('is_personal_cache', true)->pluck('name', 'id')) // Personal cache options
																->searchable()
																->requiredIf('quantity_kept_by_requester', '>', 0)
																->visible(fn ($get) => $get('quantity_kept_by_requester') > 0)
																->columnSpan(3), // Span half width

														  // TODO: Add validation rule: Sum of inputs must equal quantity_shipped
													  ])
										 // Custom validation rule for the sum check across repeater items
											 ->rule(function () {
											 return function (string $attribute, $value, \Closure $fail) {
												 foreach($value as $key => $item) {
													 $shipped = (int)($item['quantity_shipped'] ?? 0);
													 $used = (int)($item['quantity_used'] ?? 0);
													 $returned = (int)($item['quantity_returned_to_storage'] ?? 0);
													 $kept = (int)($item['quantity_kept_by_requester'] ?? 0);
													 $lost = (int)($item['quantity_lost_damaged'] ?? 0);

													 if (($used + $returned + $kept + $lost) !== $shipped) {
														 // Provide index-based feedback (key is usually numeric index)
														 $itemName = $item['item_name'] ?? ('Item #' . ($key + 1));
														 $fail("For {$itemName}, the sum of Used ({$used}), Returned ({$returned}), Kept ({$kept}), and Lost ({$lost}) must equal the Quantity Shipped ({$shipped}).");
													 }
												 }
											 };
										 }),

									 Textarea::make('reconciliation_notes')
											 ->label('Overall Reconciliation Notes')
											 ->nullable()
											 ->columnSpanFull(),
								 ])
				// --- Action Logic ---
						  ->action(function (ConventionRequest $record, array $data) {
					// $data contains 'reconciliation_date', 'reconciler_id', 'reconciliation_notes', 'reconciled_items' array
					Log::info('Reconcile Action Data Received:', ['data' => $data]);

					if (!isset($data['reconciled_items'])) {
						Notification::make()->title('Reconciliation Error')->body('No item data received.')->danger()->send();
						return;
					}

					$reconciliationDate = $data['reconciliation_date'];
					$reconcilerId = $data['reconciler_id'];
					$overallNotes = $data['reconciliation_notes']; // May be null

					try {
						DB::transaction(function() use ($record, $data, $reconciliationDate, $reconcilerId, $overallNotes) {
							foreach($data['reconciled_items'] as $itemData) {
								$requestItem = ConventionRequestItem::find($itemData['request_item_id']);
								if (!$requestItem) {
									Log::warning('Reconcile Action: Could not find parent ConventionRequestItem', ['data' => $itemData]);
									continue;
								}

								// Prepare data for Reconciliation record
								$reconciliationData = [
									'convention_request_id' => $record->id,
									'item_id' => $requestItem->item_id, // Get item_id from parent
									'quantity_shipped' => (int)($itemData['quantity_shipped'] ?? 0),
									'quantity_used' => (int)($itemData['quantity_used'] ?? 0),
									'quantity_returned_to_storage' => (int)($itemData['quantity_returned_to_storage'] ?? 0),
									'return_location_id' => $itemData['return_location_id'] ?? null,
									'quantity_kept_by_requester' => (int)($itemData['quantity_kept_by_requester'] ?? 0),
									'requester_kept_location_id' => $itemData['requester_kept_location_id'] ?? null,
									'quantity_lost_damaged' => (int)($itemData['quantity_lost_damaged'] ?? 0),
									'reconciliation_date' => $reconciliationDate,
									'reconciler_id' => $reconcilerId,
									'notes' => null, // Can add item-specific notes field later if needed
								];

								// 1. Create or Update ConventionReconciliation Record
								// Use updateOrCreate to handle cases where reconciliation is edited/redone
								$reconciliation = ConventionReconciliation::updateOrCreate(
									[
										'convention_request_id' => $record->id,
										'item_id' => $requestItem->item_id,
									],
									$reconciliationData
								);

								// 2. Generate Inventory Movements based on reconciliation data
								//    (We'll only create movements for quantities > 0)

								// Return to Storage Movement (Positive)
								if ($reconciliation->quantity_returned_to_storage > 0 && $reconciliation->return_location_id) {
									InventoryMovement::create([
																  'item_id' => $reconciliation->item_id,
																  'quantity' => $reconciliation->quantity_returned_to_storage, // Positive
																  'from_location_id' => null, // Coming back from 'the void' (convention)
																  'to_location_id' => $reconciliation->return_location_id,
																  'user_id' => $reconcilerId,
																  'movement_type' => 'convention_return',
																  'related_entity_type' => ConventionRequest::class,
																  'related_entity_id' => $record->id,
																  'notes' => "Returned from request #{$record->id}",
															  ]);
								}

								// Kept by Requester Movement (Positive)
								if ($reconciliation->quantity_kept_by_requester > 0 && $reconciliation->requester_kept_location_id) {
									InventoryMovement::create([
																  'item_id' => $reconciliation->item_id,
																  'quantity' => $reconciliation->quantity_kept_by_requester, // Positive
																  'from_location_id' => null,
																  'to_location_id' => $reconciliation->requester_kept_location_id,
																  'user_id' => $reconcilerId,
																  'movement_type' => 'convention_return', // Or 'transfer_to_cache'
																  'related_entity_type' => ConventionRequest::class,
																  'related_entity_id' => $record->id,
																  'notes' => "Kept by requester from request #{$record->id}",
															  ]);
								}

								// Optional: Create negative movements for audit trail of usage/loss
								// These don't affect stock levels further if shipment already decremented stock
								if ($reconciliation->quantity_used > 0) {
									InventoryMovement::create([
																  'item_id' => $reconciliation->item_id,
																  'quantity' => -$reconciliation->quantity_used, // Negative
																  'from_location_id' => null,
																  'to_location_id' => null, // Represents usage/gone
																  'user_id' => $reconcilerId,
																  'movement_type' => 'convention_usage',
																  'related_entity_type' => ConventionRequest::class,
																  'related_entity_id' => $record->id,
																  'notes' => "Used during request #{$record->id}",
															  ]);
								}
								if ($reconciliation->quantity_lost_damaged > 0) {
									InventoryMovement::create([
																  'item_id' => $reconciliation->item_id,
																  'quantity' => -$reconciliation->quantity_lost_damaged, // Negative
																  'from_location_id' => null,
																  'to_location_id' => null, // Represents loss
																  'user_id' => $reconcilerId,
																  'movement_type' => 'loss_adjustment', // Or 'convention_loss'
																  'related_entity_type' => ConventionRequest::class,
																  'related_entity_id' => $record->id,
																  'notes' => "Lost/Damaged during request #{$record->id}",
															  ]);
								}
							}

							// 3. Update Overall Request Status
							$record->update([
												'status' => ConventionRequestStatus::Reconciled, // Or could be 'Closed'
												'admin_notes' => $overallNotes ?? $record->admin_notes // Append or replace notes
											]);
						});

						Notification::make()->title('Reconciliation Saved')->success()->send();

					} catch (\Exception $e) {
						Log::error('Reconcile Action Failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
						Notification::make()->title('Reconciliation Failed')->body('An error occurred. Please check logs.')->danger()->send();
					}

				})
						  ->modalWidth('3xl') // Wider modal needed
						  ->modalSubmitActionLabel('Save Reconciliation & Update Stock'),
		];
	}
}