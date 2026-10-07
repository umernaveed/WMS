<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomsOfficeCode extends Model
{
    protected $table = 'customs_office_codes';
    protected $primaryKey = 'id';
    protected $fillable = [ 'id', 'customs_office', 'code' ];
	
	protected $dates = [
        'created_at',
        'updated_at'
    ];
}