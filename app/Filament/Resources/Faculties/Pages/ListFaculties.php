<?php

namespace App\Filament\Resources\Faculties\Pages;

use App\Filament\Resources\Faculties\FacultyResource;
use Filament\Resources\Pages\ListRecords;

class ListFaculties extends ListRecords
{
    protected static string $resource = FacultyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action — faculty nodes are managed via seeders
        ];
    }
}

