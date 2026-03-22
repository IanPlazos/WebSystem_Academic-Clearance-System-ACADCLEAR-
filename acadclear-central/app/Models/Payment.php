<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'tenant_id', 'subscription_id', 'amount', 'payment_date',
        'payment_method', 'reference_number', 'transaction_id',
        'status', 'notes', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function getFormattedAmountAttribute()
    {
        return '₱' . number_format($this->amount, 2);
    }
}