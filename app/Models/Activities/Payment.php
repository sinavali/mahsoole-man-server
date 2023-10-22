<?php

namespace App\Models\Activities;

use App\Models\Users\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id', 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uuid', 'user_uuid');
    }
}
