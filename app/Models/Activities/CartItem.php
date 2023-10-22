<?php

namespace App\Models\Activities;

use App\Models\Products\Product;
use App\Models\Users\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uuid', 'user_uuid');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'uuid', 'product_uuid');
    }
}
