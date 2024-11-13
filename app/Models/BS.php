<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BS extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'full_name', 'ha', 'dr', 'rpo', 'rto', 'b_r_s_id'];
    public $timestamps = false;
}
