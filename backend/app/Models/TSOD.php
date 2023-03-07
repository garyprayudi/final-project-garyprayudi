<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TSOD extends Model
{
    use HasFactory;

    protected $table = 't_sod';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $guarded = ['id'];
}
