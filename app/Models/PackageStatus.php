<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageStatus extends Model
{
    protected $table = "package_status";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['id','name'];
	
}
