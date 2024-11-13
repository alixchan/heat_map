<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolarWinds extends Model
{
    use HasFactory;

    protected $fillable = ['sw_name', 'sw_server', 'sw_domain'];
    public $timestamps = false;

}
