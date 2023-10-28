<?php

namespace App\Models\Activities;

use App\Models\Products\Product;
use App\Models\Users\User\User;
use App\Models\Users\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CartItem extends Model
{

    protected $fillable = [
        'user_uuid',
        'product_uuid',
        'product_vendor_uuid',
        'quantity'
    ];
    //
    public static function addToCart($product_uuid, $user_uuid)
    {
        $data = ['status' => 'ok', 'data' => ''];
        $isProductOk = Product::productCanGetProccessed($product_uuid, $user_uuid);
        if ($isProductOk['status'] !== 'ok') {
            $data['status'] = 'error';
            $data['data'] = $isProductOk['data'];
            return $data;
        }
        $product = $isProductOk['data'];
        $exists = self::existInCart($product_uuid, $user_uuid);
        // return $exists;
        //
        if ($exists) { // if already exists in cart, edit cartItem quantity
            $cartItem = self::where('user_uuid', $user_uuid)->where('product_uuid', $product_uuid)->latest()->first();
            if ($cartItem) {
                $cartItem->quantity++;
                if (!!$cartItem->update()) {
                    $data['status'] = 'ok';
                    $data['data'] = true;
                    return $data;
                } else {
                    $data['status'] = 'error';
                    $data['data'] = 'افزودن به سبد خرید با مشکل مواجه شد. لطفا دوباره امتحان کنید.';
                    return $data;
                }
            } else {
                $data['status'] = 'error';
                $data['data'] = 'محصول در سبد خرید یافت نشد. لطفا دوباره امتحان کنید.';
                return $data;
            }
        } else { // if not, create a new cartItem
            $data = [
                'user_uuid' => $user_uuid,
                'product_uuid' => $product_uuid,
                'product_vendor_uuid' => $product->vendor->uuid,
                'quantity' => 1
            ];
            if (!!self::create($data)) {
                $data['status'] = 'ok';
                $data['data'] = true;
                return $data;
            } else {
                $data['status'] = 'error';
                $data['data'] = 'افزودن به سبد خرید با مشکل مواجه شد. لطفا دوباره امتحان کنید.';
                return $data;
            }
        }
    }
    public static function removeFromCart($id, $user_uuid)
    {
        return !!self::where('id', $id)->where('user_uuid', $user_uuid)->delete();
    }
    public static function removeFromCartById($id)
    {
        return !!self::where('id', $id)->delete();
    }
    public static function getCart($uuid)
    {
        $cartItems = self::where('user_uuid', $uuid)->with('product', 'vendor')->latest()->get();
        $cartItems = self::canCartItemsGetProccessed($cartItems);
        $cartItems = self::proccessCartItems($cartItems);
        return $cartItems;
    }
    public static function canCartItemsGetProccessed($cartItems)
    {
        for ($i = 0; $i < count($cartItems); $i++) {
            $cartItems[$i]['proccessable'] = ['status' => true];
            // validate vendor
            if (!$cartItems[$i]->vendor) {
                $cartItems[$i]['proccessable'] = ['status' => false, 'message' => 'فروشگاه حذف و یا موقتاً از دسترس خارج شده است.'];
                continue;
            }
            if (!$cartItems[$i]->vendor->active) {
                $cartItems[$i]['proccessable'] = ['status' => false, 'message' => 'فروشگاه موقتاً غیر فعال است.'];
                continue;
            }
            // validate product

            if (!$cartItems[$i]->product) {
                $cartItems[$i]['proccessable'] = ['status' => false, 'message' => 'محصول حذف و یا موقتاً از دسترس خارج شده است.'];
                continue;
            }
            if ($cartItems[$i]->product->status !== 'published') {
                $cartItems[$i]['proccessable'] = ['status' => false, 'message' => 'فروشنده موقتاً محصول را از دسترس خارج کرده است'];
                continue;
            }
            if ($cartItems[$i]->product->active !== 1) {
                $cartItems[$i]['proccessable'] = ['status' => false, 'message' => 'محصول در صف تایید است.'];
                continue;
            }
            if ($cartItems[$i]->product->quantity < 1) {
                $cartItems[$i]['proccessable'] = ['status' => false, 'message' => 'موجودی محصول کافی نمی باشد.'];
                continue;
            }
            if ($cartItems[$i]->quantity > $cartItems[$i]->product->quantity) {
                $cartItems[$i]['proccessable'] = ['status' => false, 'message' => 'این تعداد از این محصول موجود نمی باشد.'];
                continue;
            }
        }
        return $cartItems;
    }
    public static function proccessCartItems($cartItems)
    {
        for ($i = 0; $i < count($cartItems); $i++) {
            $featuredImage = $cartItems[$i]->product->getMedia('featured_image')->first();
            $cartItems[$i]->product['featured_image'] = ($featuredImage ? $featuredImage->getDiskPath() : false);
            // make hiddens
            $cartItems[$i]->makeHidden('created_at', 'updated_at', 'user_uuid', 'product_uuid', 'product_vendor_uuid');
            if ($cartItems[$i]->product)
                $cartItems[$i]->product->makeHidden('media', 'content', 'created_at', 'id', 'quantity', 'sku', 'status', 'updated_at', 'active', 'vendor_uuid');
            if ($cartItems[$i]->vendor)
                $cartItems[$i]->vendor->makeHidden('created_at', 'updated_at', 'active', 'email', 'email_confirmed', 'id', 'slug');
        }
        return $cartItems;
    }
    public static function getCartCount($uuid)
    {
        return self::where('user_uuid', $uuid)->get()->count();
    }
    public static function existInCart($product_uuid, $user_uuid)
    {
        return self::where('user_uuid', $user_uuid)->where('product_uuid', $product_uuid)->get()->count() > 0;
    }
    public static function getCartItem($id)
    {
        return self::where('id', $id)->first();
    }
    public static function getCartItemByProductId($product_uuid, $user_uuid)
    {
        return self::where('user_uuid', $user_uuid)->where('product_uuid', $product_uuid)->latest()->first();
    }
    public static function cartItemQuantityGoUp($id, $user_uuid)
    {
        $data = ['status' => 'ok', 'data' => ''];
        //
        $cartItem = self::getCartItem($id);
        if ($cartItem) {
            if ($cartItem->product->quantity > $cartItem->quantity) {
                $cartItem->quantity++;
                if ($cartItem->update()) {
                    $data['data'] = self::getCart($user_uuid);
                    $data['status'] = 'ok';
                } else {
                    $data['status'] = 'error';
                    $data['data'] = 'افزودن به تعداد محصول با خطا مواجه شد. لطفا دوباره امتحان کنید.';
                }
                return $data;
            } else {
                $data['status'] = 'error';
                $data['data'] = 'موجودی محصول کافی نمی باشد.';
                return $data;
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = "این محصول در سبد خرید شما وجود ندارد.";
            return $data;
        }
    }
    public static function cartItemQuantityGoDown($id, $user_uuid)
    {
        $data = ['status' => 'ok', 'data' => ''];
        //
        $cartItem = self::getCartItem($id);
        if ($cartItem) {
            if ($cartItem->quantity > 1) {
                $cartItem->quantity--;
                if ($cartItem->update()) {
                    $data['data'] = self::getCart($user_uuid);
                    $data['status'] = 'ok';
                } else {
                    $data['status'] = 'error';
                    $data['data'] = 'کاهش تعداد محصول با خطا مواجه شد. لطفا دوباره امتحان کنید.';
                }
                return $data;
            } else {
                $data['data'] = 'نمیتوانید کم تر از 1 عدد از محصول را در سبد خرید داشته باشید.';
                $data['status'] = 'error';
                return $data;
            }
        } else {
            $data['status'] = 'error';
            $data['message'] = "این محصول در سبد خرید شما وجود ندارد.";
            return $data;
        }
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uuid', 'user_uuid');
    }
    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'uuid', 'product_uuid');
    }
    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class, 'uuid', 'product_vendor_uuid');
    }
}
