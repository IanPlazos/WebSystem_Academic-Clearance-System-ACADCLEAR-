<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'max_students', 
        'has_advanced_reports', 'has_multi_campus', 
        'has_custom_branding', 'has_api_access', 'features'
    ];

    protected $casts = [
        'features' => 'array',
        'has_advanced_reports' => 'boolean',
        'has_multi_campus' => 'boolean',
        'has_custom_branding' => 'boolean',
        'has_api_access' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function getPriceFormattedAttribute()
    {
        return '₱' . number_format($this->price, 2);
    }

    public function getMaxStudentsFormattedAttribute()
    {
        return $this->max_students ? number_format($this->max_students) : 'Unlimited';
    }
}