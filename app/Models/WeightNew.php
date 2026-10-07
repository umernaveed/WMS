<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeightNew extends Model
{
    protected $table = "weight_new";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['id','courier_id','weight','price'];
}
