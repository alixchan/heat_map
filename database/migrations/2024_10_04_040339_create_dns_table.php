<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dns', function (Blueprint $table) {
            $table->id();
            $table->string('dns_name');
            $table->text('dns_ip')->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dns');
    }
};
