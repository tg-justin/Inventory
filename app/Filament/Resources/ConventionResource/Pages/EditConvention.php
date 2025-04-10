<?php

namespace App\Filament\Resources\ConventionResource\Pages;

use App\Filament\Resources\ConventionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditConvention extends EditRecord
{
	protected static string $resource = ConventionResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\DeleteAction::make(),
		];
	}
}
