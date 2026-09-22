<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            if (!Schema::hasColumn('anime', 'themes')) {
                $table->text('themes')->nullable()->after('popularity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            if (Schema::hasColumn('anime', 'themes')) {
                $table->dropColumn('themes');
            }
        });
    }
};