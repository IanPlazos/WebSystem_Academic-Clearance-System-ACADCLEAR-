<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'slug', 'domain', 'database', 'status',
        'suspended_at', 'suspension_reason', 'logo',
        'primary_color', 'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'suspended_at' => 'datetime',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->latest();
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->activeSubscription()->exists();
    }

    public function suspend($reason = null)
    {
        $this->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => $reason
        ]);
    }

    public function activate()
    {
        $this->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspension_reason' => null
        ]);
    }
}