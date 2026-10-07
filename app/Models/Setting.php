<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = "settings";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['id','site_name','site_email','site_logo','from_email','site_title','phone_number'];
	
}
