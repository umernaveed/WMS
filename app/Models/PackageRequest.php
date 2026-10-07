<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PackageRequest extends Model
{
    protected $table = "package_requests";
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $fillable = ['id','courier_id','mailbox','tracking_no','status','created_at','updated_at'];
}
