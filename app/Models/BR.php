<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BR extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'status', 'ha', 'dr', 'rpo', 'rto'];
    public $timestamps = false;
}
