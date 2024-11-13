<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RSP extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'system_platform', 'host', 'os', 'fault_tolerance', 'role', 'b_r_s_id', 'b_s_id'];
    public $timestamps = false;
}
