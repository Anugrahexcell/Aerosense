<?php

namespace Database\Seeders;

use App\Models\EducationArticle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EducationArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title'   => 'Memahami Polutan Udara dan Dampaknya',
                'excerpt' => 'Pelajari tentang berbagai jenis polutan udara seperti PM2.5, PM10, CO, NO2, SO2, dan O3. Ketahui bagaimana partikel-partikel ini mempengaruhi kesehatan pernapasan dan kardiovaskular Anda.',
                'category' => 'Polutan',
                'reading_time_minutes' => 5,
                'icon_type' => 'book',
            ],
            [
                'title'   => 'Kualitas Udara Indoor vs Outdoor',
                'excerpt' => 'Tahukah Anda bahwa kualitas udara dalam ruangan bisa 2-5 kali lebih buruk dari luar? Pelajari cara menjaga kualitas udara di ruang kuliah, laboratorium, dan tempat tinggal Anda.',
                'category' => 'Indoor',
                'reading_time_minutes' => 5,
                'icon_type' => 'home',
            ],
            [
                'title'   => 'Peran Vegetasi dalam Menjernihkan Udara',
                'excerpt' => 'Pelajari tentang berbagai jenis polutan udara seperti PM2.5, PM10, CO, NO2, SO2, dan O3. Ketahui bagaimana partikel-partikel ini mempengaruhi kesehatan pernapasan dan kardiovaskular Anda.',
                'category' => 'Lingkungan',
                'reading_time_minutes' => 5,
                'icon_type' => 'leaf',
            ],
            [
                'title'   => 'Panduan Aktivitas Luar Ruangan saat AQI Tinggi',
                'excerpt' => 'Mengetahui kapan aman untuk berolahraga atau beraktivitas di luar ruangan sangat penting. Pelajari panduan praktis berdasarkan indeks kualitas udara (AQI).',
                'category' => 'Panduan',
                'reading_time_minutes' => 4,
                'icon_type' => 'shield',
            ],
            [
                'title'   => 'Teknologi Sensor Kualitas Udara Modern',
                'excerpt' => 'Bagaimana sensor IoT bekerja untuk memantau CO₂, suhu, dan kelembapan secara real-time? Pelajari teknologi di balik sistem AeroSense.',
                'category' => 'Teknologi',
                'reading_time_minutes' => 6,
                'icon_type' => 'chip',
            ],
            [
                'title'   => 'Dampak Kualitas Udara Buruk pada Produktivitas',
                'excerpt' => 'Penelitian menunjukkan bahwa kualitas udara yang buruk dapat menurunkan kemampuan kognitif hingga 50%. Pelajari bagaimana udara bersih meningkatkan fokus belajar.',
                'category' => 'Kesehatan',
                'reading_time_minutes' => 5,
                'icon_type' => 'brain',
            ],
        ];

        foreach ($articles as $data) {
            EducationArticle::firstOrCreate(
                ['slug' => Str::slug($data['title'])],
                array_merge($data, [
                    'slug'         => Str::slug($data['title']),
                    'content'      => '<p>' . $data['excerpt'] . '</p>',
                    'is_published' => true,
                    'published_at' => now()->subDays(rand(1, 30)),
                ])
            );
        }
    }
}
