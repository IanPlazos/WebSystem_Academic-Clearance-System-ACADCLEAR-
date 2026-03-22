<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id', 'plan_id', 'starts_at', 'ends_at',
        'trial_ends_at', 'status', 'amount_paid',
        'payment_method', 'transaction_id', 'meta'
    ];

    protected $casts = [
        'meta' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && 
               $this->starts_at <= now() && 
               $this->ends_at >= now();
    }

    public function isExpired()
    {
        return $this->ends_at < now() || $this->status === 'expired';
    }
}