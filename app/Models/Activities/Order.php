<?php

namespace App\Models\Activities;

use App\Models\Users\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uuid', 'user_uuid');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'id', 'payment_id');
    }

    public function discount(): HasOne
    {
        return $this->hasOne(Discount::class, 'id', 'discount_id');
    }
}
