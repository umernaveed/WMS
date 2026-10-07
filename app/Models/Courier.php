<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    protected $table = "couriers";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['saas_tenant_id','saas_last_synced_at','name','logo','api_key','email','is_courier','base_url','password','hint_password','rate','state', 'city','zip','local_address','phone','description','awb_number','status','updated_at','created_at'];
}
