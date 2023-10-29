<?php

namespace App\Filament\Resources\PermanenceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PermanenceResource;
use App\Filament\Resources\PermanenceResource\Widgets\PermanenceList;

class EditPermanence extends EditRecord
{
    protected static string $resource = PermanenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            PermanenceList::class
        ];
    }
}
