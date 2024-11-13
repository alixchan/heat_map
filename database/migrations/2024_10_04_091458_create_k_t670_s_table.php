<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('k_t670_s', function (Blueprint $table) {
            $table->id();
            $table->string('br_code');
            $table->text('br_name');
            $table->string('br_rto')->nullable();
            $table->string('br_rpo')->nullable();
            $table->string('br_criticaty')->nullable();
            $table->string('service')->nullable();
            $table->string('service_name')->nullable();
            $table->string('service_owner')->nullable();
            $table->string('service_rto')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('k_t670_s');
    }
};