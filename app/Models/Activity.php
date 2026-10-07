<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = "activities";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['id','type','activity','added_by','created_at'];	
}
