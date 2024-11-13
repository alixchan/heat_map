<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('r_s_p_s', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('code');
            $table->string('name');
            $table->string('system_platform');
            $table->string('host')->nullable();
            $table->string('os')->nullable();
            $table->string('fault_tolerance')->nullable();
            $table->string('role')->nullable();
            
            $table->unsignedBigInteger('b_r_s_id');
            $table->foreign('b_r_s_id')->references('id')->on('b_r_s');  

            $table->unsignedBigInteger('b_s_id');
            $table->foreign('b_s_id')->references('id')->on('b_s');  
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_s_p_s');
    }
};
