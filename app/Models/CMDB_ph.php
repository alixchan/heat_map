<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CMDB_ph extends Model
{
    use HasFactory;

    protected $fillable = ['ci_name', 'ci_vendor', 'ci_os', 'ci_dns', 'hpsm_id', 'cluster',
    'host_id', 'sw_node_id', 'sw_node_name', 'ilo_host_name', 'vcenter_name'];

    public $timestamps = false;
}
