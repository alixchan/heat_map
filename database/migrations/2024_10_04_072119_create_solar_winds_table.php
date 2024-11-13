<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_winds', function (Blueprint $table) {
            $table->id();
            $table->string('sw_name');
            $table->string('sw_server')->nullable();
            $table->string('sw_domain')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_winds');
    }
};
