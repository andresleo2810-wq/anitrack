<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            if (!Schema::hasColumn('anime', 'synopsis')) {
                $table->text('synopsis')->nullable()->after('image_url');
            }
            if (!Schema::hasColumn('anime', 'type')) {
                $table->string('type')->nullable()->after('synopsis');
            }
            if (!Schema::hasColumn('anime', 'status')) {
                $table->string('status')->nullable()->after('type');
            }
            if (!Schema::hasColumn('anime', 'season')) {
                $table->string('season')->nullable()->after('status');
            }
            if (!Schema::hasColumn('anime', 'year')) {
                $table->unsignedSmallInteger('year')->nullable()->after('season');
            }
            if (!Schema::hasColumn('anime', 'studios')) {
                $table->string('studios')->nullable()->after('year');
            }
            if (!Schema::hasColumn('anime', 'popularity')) {
                $table->unsignedInteger('popularity')->nullable()->after('studios');
            }
        });
    }

    public function down(): void
    {
        // Irreversible seguro
    }
};