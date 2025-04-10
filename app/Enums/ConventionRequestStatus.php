<?php

namespace App\Enums;

// Use Filament Color interface for direct color mapping (optional but nice)
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ConventionRequestStatus: string implements HasLabel, HasColor
{
	case Draft = 'draft';
	case Submitted = 'submitted';
	case Approved = 'approved';
	case Rejected = 'rejected';
	// case PartiallyShipped = 'partially_shipped'; // Consider if needed
	case Shipped = 'shipped';
	case Received = 'received'; // Optional state if requester confirms receipt
	case Reconciling = 'reconciling';
	case Reconciled = 'reconciled'; // Fully reconciled by admin
	case Closed = 'closed'; // Final state, nothing more to do

	// Method required by HasLabel interface (for display)
	public function getLabel(): ?string
	{
		return match ($this) {
			self::Draft => 'Draft',
			self::Submitted => 'Submitted',
			self::Approved => 'Approved',
			self::Rejected => 'Rejected',
			// self::PartiallyShipped => 'Partially Shipped',
			self::Shipped => 'Shipped',
			self::Received => 'Received',
			self::Reconciling => 'Reconciling',
			self::Reconciled => 'Reconciled',
			self::Closed => 'Closed',
		};
	}

	// Method required by HasColor interface (for badges)
	public function getColor(): string | array | null
	{
		return match ($this) {
			self::Draft => 'gray',
			self::Submitted => 'warning',
			self::Approved => 'primary', // Or 'info'
			self::Rejected => 'danger',
			// self::PartiallyShipped => 'info',
			self::Shipped => 'info', // Or 'success' depending on context
			self::Received => 'success',
			self::Reconciling => 'warning',
			self::Reconciled => 'success',
			self::Closed => 'gray', // Or 'success'
		};
	}

	// Optional: Helper method to get all values for Filament Select options
	public static function options(): array
	{
		return collect(self::cases())
			->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])
			->toArray();
	}
}