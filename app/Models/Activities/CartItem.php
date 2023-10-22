<?php

namespace App\Models\Activities;

use App\Models\Products\Product;
use App\Models\Users\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{

    protected $fillable = [
        'user_uuid',
        'product_uuid',
        'quantity'
    ];
    //
    public static function addToCart($uuid, $user_uuid)
    {
        $product = Product::productCanGetProccessed($uuid);
        if (!$product[0])
            return $product[1];
        $product = $product[1];
        $item = self::existInCart($uuid, $user_uuid);
        if ($item) {
            if ($item->quantity++)
                return true;
            else
                return false;
        } else {
            if (self::create(['user_uuid' => $user_uuid, 'product_uuid' => $uuid, 'quantity' => 1]))
                return true;
            else
                return false;
        }
    }
    public static function existInCart($uuid, $user_uuid)
    {
        $item = self::where('user_uuid', $user_uuid)->where('product_uuid', $uuid)->get();
        if (count($item))
            return $item[0];
        return false;
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uuid', 'user_uuid');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'uuid', 'product_uuid');
    }
}
