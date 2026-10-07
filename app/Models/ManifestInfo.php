<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManifestInfo extends Model
{
    protected $table = "manifest_info";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = [ 
		'id', 
		'cargo_company', 
		'custom_office_code', 
		'date_of_departure', 
		'shipping_agent_code', 
		'exporter_name', 
		'loading_port', 
		'exporter_address', 
		'master_air_billing_no'
	];
}
