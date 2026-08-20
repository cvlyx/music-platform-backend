<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'tx_ref',
        'amount',
        'currency',
        'plan',
        'status',
        'method',
        'paychangu_data',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paychangu_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
