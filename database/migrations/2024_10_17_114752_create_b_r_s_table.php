<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b_r_s', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('code');
            $table->string('name');
            $table->string('status');
            $table->string('ha');
            $table->string('dr');
            $table->string('rpo')->nullable();
            $table->string('rto')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b_r_s');
    }
};
