<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class VendorBill extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id','vendor_id','invoice_number','amount','billing_date','status','file_path','created_by'
    ];

    protected $casts = [
        'billing_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
