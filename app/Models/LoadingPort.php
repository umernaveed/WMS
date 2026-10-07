<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoadingPort extends Model
{
    protected $table = 'loading_ports';
    protected $primaryKey = 'id';
    protected $fillable = [ 'id', 'name', 'code', 'city', 'state' ];
	
	protected $dates = [
        'created_at',
        'updated_at'
    ];
}