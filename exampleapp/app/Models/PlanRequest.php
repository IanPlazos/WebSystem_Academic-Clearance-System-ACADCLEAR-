<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanRequest extends Model
{
    protected $fillable = [
        'tenant_slug',
        'tenant_name',
        'plan_name',
        'institution_name',
        'contact_person',
        'email',
        'contact_number',
        'payment_method',
        'amount',
        'payment_reference',
        'gcash_number',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'notes',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function getConnectionName()
    {
        return config('database.default');
    }
}
