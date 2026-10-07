<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomRate extends Model
{
    protected $table = "custom_rates";
    public $timestamps = false;
    protected $primaryKey = "custom_rate_id";
    protected $fillable = ['custom_rate_id','name','icd','scf','icdgct','enl','added_by'];
	
}
