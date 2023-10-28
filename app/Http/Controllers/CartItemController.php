<?php

namespace App\Http\Controllers;

use App\Models\Activities\CartItem;
use App\Models\Products\Product;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public static function getCart(Request $req)
    {
        $cart = CartItem::getCart($req->user()->uuid);
        return response()->json($cart);
    }
    public static function getCartCount(Request $req)
    {
        return response()->json(CartItem::getCartCount($req->user()->uuid));
    }
    public static function addToCart(Request $req)
    {
        if (!$req->uuid)
            return response()->json('شناسه کالا را ارسال کنید.', 422);
        $res = CartItem::addToCart($req->uuid, $req->user()->uuid);
        if ($res['status'] === 'ok')
            return response()->json(CartItem::getCartCount($req->user()->uuid));
        else
            return response()->json($res['data'], 500);
    }
    public static function removeFromCart(Request $req)
    {
        if (!$req->id)
            return response()->json('شناسه محصول را ارسال کنید', 422);
        CartItem::removeFromCart($req->id, $req->user()->uuid);
        return response()->json(CartItem::getCart($req->user()->uuid));
    }
    public static function cartItemQuantityGoUp(Request $req)
    {
        if (!$req->id)
            return response()->json('شناسه محصول را ارسال کنید', 422);
        return response()->json(CartItem::cartItemQuantityGoUp($req->id, $req->user()->uuid));
    }
    public static function cartItemQuantityGoDown(Request $req)
    {
        if (!$req->id)
            return response()->json('شناسه محصول را ارسال کنید', 422);
        return response()->json(CartItem::cartItemQuantityGoDown($req->id, $req->user()->uuid));
    }
}
