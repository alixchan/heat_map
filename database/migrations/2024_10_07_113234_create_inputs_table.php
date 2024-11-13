<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inputs', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('br_code')->nullable();
            $table->string('br_name')->nullable();
            $table->string('br_criticaty')->nullable();
            $table->string('br_rto')->nullable();
            $table->string('br_rpo')->nullable();
            $table->string('bs_code');
            $table->string('bs_name');
            $table->string('bs_rto');
            $table->string('bs_rpo');
            $table->string('rsp')->nullable();
            $table->string('rsp_system_platform')->nullable();
            $table->string('rsp_server')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inputs');
    }
};
