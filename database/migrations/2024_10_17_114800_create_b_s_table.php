<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('b_s', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('code');
            $table->string('name');
            $table->text('full_name');
            $table->string('ha');
            $table->string('dr');
            $table->string('rpo')->nullable();
            $table->string('rto')->nullable();
            $table->unsignedBigInteger('b_r_s_id');
            $table->foreign('b_r_s_id')->references('id')->on('b_r_s');         
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('b_s');
    }
};
