<?php

namespace App\Filament\Resources\EducationArticles\Pages;

use App\Filament\Resources\EducationArticles\EducationArticleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEducationArticle extends EditRecord
{
    protected static string $resource = EducationArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
