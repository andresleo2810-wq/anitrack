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
    Schema::create('anime', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('mal_id')->unique(); // ID de MyAnimeList
        $table->string('title'); // Título principal
        $table->string('title_english')->nullable(); // Título en inglés
        $table->text('synopsis')->nullable(); // Sinopsis
        $table->string('type')->nullable(); // TV, Movie, OVA, etc.
        $table->integer('episodes')->nullable();
        $table->string('status')->nullable(); // Airing, Completed, etc.
        $table->string('rating')->nullable(); // PG-13, R, etc.
        $table->float('score')->nullable(); // Puntuación promedio
        $table->integer('scored_by')->nullable();
        $table->integer('rank')->nullable();
        $table->integer('popularity')->nullable();
        $table->integer('members')->nullable();
        $table->integer('favorites')->nullable();
        $table->string('source')->nullable(); // Manga, Light novel, etc.
        $table->string('duration')->nullable(); // Duración por episodio
        $table->string('trailer_url')->nullable(); // URL del trailer
        $table->string('image_url')->nullable(); // URL del poster
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::dropIfExists('anime');   // ← singular, igual que en up()
}
};
