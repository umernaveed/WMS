<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManifestPackage extends Model
{
    protected $table = 'manifest_packeges';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $fillable = [ 'id', 'manifest_id', 'packeges_id' ];

    public function info()
	{
		return $this->hasOne( 'App\Models\Manifest', 'id', 'manifest_id' );
    }

    public function package()
	{
		return $this->hasOne( 'App\Models\Package', 'packeg_id', 'packeges_id' );
    }
    
    public function user()
	{
		return $this->belongsTo( 'App\Models\User', 'mailbox', 'mailbox' );
    }
}