<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
	const CBM_TO_CUFT = 35.3147;

    protected $table = 'packages';
    public $timestamps = false;
    protected $primaryKey = 'packeg_id';
	protected $fillable = [ 
		'packeg_id', 
		'saas_package_id',
		'saas_last_synced_at',
		'courier_id',
		'tracking_no', 
		'mailbox', 
		'user_name',
		'supplier_tracking_no',
		'courier',
		'package_type', 
		'merchant',
		'price', 
		'item_description', 
		'weight',
		'weight_unit',
		'pieces',
		'freight_type',
		'label_type',
		'entry_mode',
		'routing_status',
		'warehouse_code',
		'consignee_name_on_package',
		'unknown_reason',
		'internal_notes',
		'package_image_paths',
		'dimension_length',
		'dimension_width',
		'dimension_height',
		'dimension_unit',
		'volume_cuft',
		'volume_cbm',
		'chargeable_weight',
		'unknown_fee',
		'unknown_fee_reason',
		'capture_method',
		'idempotency_key',
		'status',
		'status_name',
		'manifest_no',
		'date_received',
		'sync_status',
		'sync_error',
		'is_invoice'
	];
	
	public function client()
	{
		return $this->hasOne( 'App\Models\User', 'mailbox', 'mailbox' );
    }	

    public function hs_code()
	{
		return $this->hasOne( 'App\Models\HSCode', 'code', 'package_type' );
    }

	public function isSeaFreight(): bool
	{
		return stripos((string) $this->freight_type, 'sea') !== false;
	}

	public function seaVolumeDisplay(): string
	{
		$cuft = (float) ($this->volume_cuft ?: $this->chargeable_weight ?: 0);
		$cbm = (float) ($this->volume_cbm ?: ($cuft > 0 ? $cuft / self::CBM_TO_CUFT : 0));

		return number_format($cuft, 2).' cu.ft / '.number_format($cbm, 3).' CBM';
	}

	public function measureDisplay(): string
	{
		if ($this->isSeaFreight()) {
			return $this->seaVolumeDisplay();
		}

		return number_format((float) $this->weight, 2).' lb';
	}
    
    
}
