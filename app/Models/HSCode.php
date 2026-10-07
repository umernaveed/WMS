<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HSCode extends Model
{
    protected $table = 'hs_codes';
    protected $primaryKey = 'id';
    protected $fillable = [ 'id', 'code', '	description' ];
	
	protected $dates = [
        'created_at',
        'updated_at'
    ];
}