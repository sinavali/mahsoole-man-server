<?php

namespace App\Http\Controllers;

use App\Models\Activities\CartItem;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public static function addToCart(Request $req)
    {
        if (!$req->uuid)
            return response()->json('شناسه کالا را ارسال کنید.', 429);
        return CartItem::addToCart($req->uuid, $req->user()->uuid);
    }
}
