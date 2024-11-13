<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KT670 extends Model
{

    use HasFactory;

    protected $fillable = ['br_code', 'br_name', 'br_rto', 'br_rpo', 'br_criticaty',
     'service', 'service_name', 'service_owner', 'service_rto'];
    public $timestamps = false;
    
}
