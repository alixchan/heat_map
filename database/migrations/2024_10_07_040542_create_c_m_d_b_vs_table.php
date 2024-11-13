<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c_m_d_b_vs', function (Blueprint $table) {
            $table->id();
            $table->string('ci_name');
            $table->string('ci_development');
            $table->string('vm_os')->nullable();
            $table->string('vm_dns_name')->nullable();
            $table->string('vm_name');
            $table->string('vm_platform');
            $table->string('vm_vcenter')->nullable();
            $table->string('vm_cluster')->nullable();
            $table->string('vm_id')->nullable();
            $table->string('sw_node_id')->nullable();
            $table->string('sw_node_name')->nullable();
            $table->string('vm_host')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c_m_d_b_vs');
    }
};
