<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dns extends Model
{
    use HasFactory;

    protected $fillable = ['dns_name', 'dns_ip'];
    public $timestamps = false;
}
