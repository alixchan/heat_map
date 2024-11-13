<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('e_s_i_s', function (Blueprint $table) {
            $table->id();
            $table->string('deployed_platform');
            $table->string('bs_name');
            $table->string('bs_code');
            $table->string('ha')->nullable();
            $table->string('dr')->nullable();
            $table->string('rpo')->nullable();
            $table->string('rto')->nullable();
            $table->string('rsp_name')->nullable();
            $table->string('rsp_system_platform')->nullable();
            $table->string('rsp_platform_version')->nullable();
            $table->string('rsp_hostname')->nullable();
            $table->string('rsp_os')->nullable();
            $table->string('rsp_os_version')->nullable();
            $table->string('rsp_fault_tolerance')->nullable();
            $table->string('rsp_fault_tolerance_role')->nullable();
            $table->string('rsp_host_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('e_s_i_s');
    }
};
