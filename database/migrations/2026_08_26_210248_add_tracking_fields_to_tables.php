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
        Schema::table('anime', function (Blueprint $table) {
            $table->integer('episodes_total')->nullable();
        });

        Schema::table('user_anime', function (Blueprint $table) {
            $table->date('started_at')->nullable();
            $table->date('finished_at')->nullable();
            $table->integer('rewatch_count')->default(0);
            $table->softDeletes();                 // ← papelera
            $table->index(['user_id', 'status']);  // ← índice rendimiento
        });
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropColumn('episodes_total');
        });

        Schema::table('user_anime', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropSoftDeletes();
            $table->dropColumn(['started_at', 'finished_at', 'rewatch_count']);
        });
    }
};
