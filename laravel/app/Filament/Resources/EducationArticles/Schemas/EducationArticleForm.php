<?php

namespace App\Filament\Resources\EducationArticles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EducationArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                // ── Judul Artikel ───────────────────────────
                TextInput::make('title')
                    ->label('Judul Artikel')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) =>
                        $set('slug', Str::slug($state))
                    )
                    ->columnSpanFull(),

                // ── Slug (auto-generated) ────────────────────
                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Otomatis diisi dari judul. Ubah jika perlu.')
                    ->columnSpanFull(),

                // ── Link Artikel Berita (external URL) ────────
                TextInput::make('external_url')
                    ->label('Link Artikel / Berita')
                    ->url()
                    ->placeholder('https://example.com/artikel-berita')
                    ->helperText('Tombol "Baca selengkapnya" akan membuka link ini di tab baru.')
                    ->prefixIcon('heroicon-m-link')
                    ->columnSpanFull(),

                // ── Ringkasan (excerpt) ──────────────────────
                Textarea::make('excerpt')
                    ->label('Ringkasan')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Ditampilkan di kartu artikel pada halaman Edukasi.')
                    ->columnSpanFull(),

                // ── Kategori & Waktu Baca ────────────────────
                Select::make('category')
                    ->label('Kategori')
                    ->options([
                        'Kesehatan'    => 'Kesehatan',
                        'Lingkungan'   => 'Lingkungan',
                        'Teknologi'    => 'Teknologi',
                        'Panduan'      => 'Panduan',
                        'Penelitian'   => 'Penelitian',
                        'Berita'       => 'Berita',
                    ])
                    ->searchable()
                    ->nullable(),

                TextInput::make('reading_time_minutes')
                    ->label('Estimasi Waktu Baca (menit)')
                    ->numeric()
                    ->default(5)
                    ->minValue(1)
                    ->maxValue(60)
                    ->suffix('menit'),

                // ── Publikasi ────────────────────────────────
                Toggle::make('is_published')
                    ->label('Publikasikan Artikel')
                    ->helperText('Artikel hanya tampil di halaman publik jika dipublikasikan.')
                    ->live()
                    ->columnSpanFull(),

                DateTimePicker::make('published_at')
                    ->label('Tanggal & Waktu Publikasi')
                    ->default(now())
                    ->required(fn ($get) => $get('is_published'))
                    ->visible(fn ($get) => $get('is_published'))
                    ->columnSpanFull(),

            ]);
    }
}
