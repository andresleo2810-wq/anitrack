<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    //public function up(): void
{
    Schema::create('genres', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('mal_id')->unique();
        $table->string('name');
        $table->timestamps();
    });
}
}
