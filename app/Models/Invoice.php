<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = "invoices";
    public $timestamps = false;
    protected $primaryKey = "invoice_id";
    protected $fillable = ['invoice_id','invoice_no','tracking_no','courier_id','mailbox_no','total','type','gct','status','email_sent'];

}
