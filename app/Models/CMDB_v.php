<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CMDB_v extends Model
{
    use HasFactory;

    protected $fillable = ['ci_name', 'ci_development', 'vm_os', 'vm_dns_name', 'vm_name', 'vm_platform', 'vm_vcenter',
    'vm_cluster', 'vm_id', 'sw_node_id', 'sw_node_name', 'vm_host'];

    public $timestamps = false;
}
