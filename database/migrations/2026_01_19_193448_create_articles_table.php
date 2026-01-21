<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->index();
            $table->string('source', 50)->index();
            $table->string('source_name')->nullable();
            $table->string('author')->nullable()->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->string('url', 2048);
            $table->string('image_url', 2048)->nullable();
            $table->string('category', 100)->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['external_id', 'source']);

            $table->fullText(['title', 'description', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
