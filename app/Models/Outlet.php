<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $table = "outlets";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['outlet_id','outlet_name','password','email','phone','updated_at'];
}
