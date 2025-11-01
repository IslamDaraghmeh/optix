<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Sale extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'patient_id',
        'items',
        'total_price',
        'paid_amount',
        'remaining_amount',
        'sale_date',
    ];

    protected $casts = [
        'items' => 'array',
        'total_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'sale_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function getIsPaidAttribute()
    {
        return $this->remaining_amount <= 0;
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->is_paid) {
            return 'Paid';
        } elseif ($this->paid_amount > 0) {
            return 'Partial';
        }
        return 'Unpaid';
    }
}
