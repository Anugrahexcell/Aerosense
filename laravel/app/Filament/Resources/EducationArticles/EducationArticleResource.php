<?php

namespace App\Filament\Resources\EducationArticles;

use App\Filament\Resources\EducationArticles\Pages\CreateEducationArticle;
use App\Filament\Resources\EducationArticles\Pages\EditEducationArticle;
use App\Filament\Resources\EducationArticles\Pages\ListEducationArticles;
use App\Filament\Resources\EducationArticles\Schemas\EducationArticleForm;
use App\Filament\Resources\EducationArticles\Tables\EducationArticlesTable;
use App\Models\EducationArticle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EducationArticleResource extends Resource
{
    protected static ?string $model = EducationArticle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static ?string $navigationLabel = 'Menu Edukasi';

    protected static ?string $modelLabel = 'Artikel Edukasi';

    protected static ?string $pluralModelLabel = 'Artikel Edukasi';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return EducationArticleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EducationArticlesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEducationArticles::route('/'),
            'create' => CreateEducationArticle::route('/create'),
            'edit' => EditEducationArticle::route('/{record}/edit'),
        ];
    }
}
