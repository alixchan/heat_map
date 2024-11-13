<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c_m_d_b_phs', function (Blueprint $table) {
            $table->id();
            $table->string('ci_name')->nullable();
            $table->string('ci_vendor')->nullable();
            $table->string('ci_os')->nullable();
            $table->string('ci_dns')->nullable();
            $table->string('hpsm_id')->nullable();
            $table->string('cluster')->nullable();
            $table->string('host_id')->nullable();
            $table->string('sw_node_id')->nullable();
            $table->string('sw_node_name')->nullable();
            $table->string('ilo_host_name')->nullable();
            $table->string('vcenter_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c_m_d_b_phs');
    }
};
