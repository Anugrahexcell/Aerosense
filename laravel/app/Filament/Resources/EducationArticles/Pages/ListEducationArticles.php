<?php

namespace App\Filament\Resources\EducationArticles\Pages;

use App\Filament\Resources\EducationArticles\EducationArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEducationArticles extends ListRecords
{
    protected static string $resource = EducationArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
