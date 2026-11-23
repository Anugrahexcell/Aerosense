<?php

namespace App\Filament\Resources\EducationArticles\Tables;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EducationArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Menu Edukasi')
            ->description('Kelola konten edukasi tentang kualitas udara untuk pengguna')
            ->columns([
                // Title + category as subtitle
                TextColumn::make('title')
                    ->label('Judul Artikel')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->category),

                // External link — clickable badge
                TextColumn::make('external_url')
                    ->label('Link Berita')
                    ->limit(40)
                    ->url(fn ($record) => $record->external_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('info')
                    ->placeholder('—'),

                // Reading time
                TextColumn::make('reading_time_minutes')
                    ->label('Waktu Baca')
                    ->suffix(' menit')
                    ->sortable(),

                // Published status badge
                IconColumn::make('is_published')
                    ->label('Publikasi')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                // Published date
                TextColumn::make('published_at')
                    ->label('Dipublikasikan')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->placeholder('Belum dipublikasikan'),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                TernaryFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->trueLabel('Dipublikasikan')
                    ->falseLabel('Draft'),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'Kesehatan'  => 'Kesehatan',
                        'Lingkungan' => 'Lingkungan',
                        'Teknologi'  => 'Teknologi',
                        'Panduan'    => 'Panduan',
                        'Penelitian' => 'Penelitian',
                        'Berita'     => 'Berita',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon('heroicon-m-pencil')
                    ->iconButton(),
                DeleteAction::make()
                    ->icon('heroicon-m-trash')
                    ->iconButton(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Konten')
                    ->icon('heroicon-m-plus')
                    ->color('primary'),
            ])
            ->emptyStateHeading('Belum Ada Artikel')
            ->emptyStateDescription('Tambahkan artikel edukasi pertama menggunakan tombol "+ Tambah Konten".')
            ->emptyStateIcon('heroicon-o-newspaper');
    }
}
