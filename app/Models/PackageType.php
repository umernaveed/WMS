<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageType extends Model
{
    protected $table = "package_types";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['id','type','code'];
}
