<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_invoice_id',
        'item_name',
        'amount',
        'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }
}