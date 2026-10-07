<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleModule extends Model
{
    protected $table = "role_modules_new";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['id','main_title','sub_title'];
	
}
