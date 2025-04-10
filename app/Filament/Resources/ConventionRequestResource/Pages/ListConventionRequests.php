<?php

namespace App\Filament\Resources\ConventionRequestResource\Pages;

use App\Filament\Resources\ConventionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConventionRequests extends ListRecords
{
	protected static string $resource = ConventionRequestResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\CreateAction::make(),
		];
	}
}
