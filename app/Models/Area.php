<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = "areas";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['id','name','value1','value2','value3'];
    protected $casts = [
        'id'=>'integer','name'=>'string','value1'=>'string','value2'=>'string','value3'=>'string'
    ];
}
