<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = "roles";
    public $timestamps = false;
    protected $primaryKey = "role_id";
    protected $fillable = ['role_id','name'];
}
