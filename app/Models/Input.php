<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Input extends Model
{
    use HasFactory;

    protected $fillable = [ 'br_code', 'br_name', 'br_criticaty', 'br_rto', 'br_rpo',
    'bs_code', 'bs_name', 'bs_rto', 'bs_rpo', 'rsp', 'rsp_system_platform', 'rsp_server'];

    public $timestamps = false;
}
