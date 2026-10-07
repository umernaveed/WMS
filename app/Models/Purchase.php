<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = "purchases";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['id','user_id','name','link','notes','qty'];
}
