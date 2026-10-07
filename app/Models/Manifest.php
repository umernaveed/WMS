<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Manifest extends Model
{
    protected $table = "manifests";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['id','saas_manifest_id','saas_last_synced_at','courier_id','master_air_billing_no','file_pdf'];
}
