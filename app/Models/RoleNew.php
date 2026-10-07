<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleNew extends Model
{
    protected $table = "roles_new";
    public $timestamps = true;
    protected $primaryKey = "role_id";
    protected $fillable = ['role_id','title','lable'];	
}
