<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    protected $table = "carriers";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['name'];
}
