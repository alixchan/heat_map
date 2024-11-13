<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ESIS extends Model
{
    use HasFactory;

    protected $fillable = ['deployed_platform', 'bs_name', 'bs_code', 'ha', 'dr', 'rpo', 'rto', 'rsp_name', 'rsp_system_platform', 
    'rsp_platform_version', 'rsp_hostname', 'rsp_os', 'rsp_os_version', 'rsp_fault_tolerance', 'rsp_fault_tolerance_role', 'rsp_host_data'];
    public $timestamps = false;
}
