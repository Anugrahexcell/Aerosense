<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->comment('Short summary shown on article card');
            $table->longText('content')->nullable()->comment('Full article body (HTML/Markdown)');
            $table->string('category', 100)->nullable();
            $table->tinyInteger('reading_time_minutes')->default(5);
            $table->string('cover_image')->nullable();
            $table->string('icon_type', 50)->nullable()->comment('Icon identifier for card display');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->comment('Admin user ID');
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_articles');
    }
};
