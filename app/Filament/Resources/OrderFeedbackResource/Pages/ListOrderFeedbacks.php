<?php

namespace App\Filament\Resources\OrderFeedbackResource\Pages;

use App\Filament\Resources\OrderFeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderFeedbacks extends ListRecords
{
    protected static string $resource = OrderFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action needed
        ];
    }
}
