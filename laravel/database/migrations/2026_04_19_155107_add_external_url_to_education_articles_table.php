<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('education_articles', function (Blueprint $table) {
            // External news/article link — admin pastes the URL here,
            // the "Baca selengkapnya" button on the public page opens this URL in a new tab.
            $table->string('external_url')->nullable()->after('slug')
                  ->comment('External news/article URL opened by "Baca selengkapnya" button');
        });
    }

    public function down(): void
    {
        Schema::table('education_articles', function (Blueprint $table) {
            $table->dropColumn('external_url');
        });
    }
};
