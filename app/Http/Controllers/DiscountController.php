<?php

namespace App\Http\Controllers;

use App\Models\Activities\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public static function getAllDiscounts(Request $req): JsonResponse
    {
        return response()->json(Discount::latest()->paginate($req->per_page ?? 50));
    }

    public static function getByVendor(Request $req): JsonResponse
    {
        return response()->json(Discount::where('vendor_uuid', $req->uuid)->latest()->paginate($req->per_page ?? 50));
    }

    public static function getByThisVendor(Request $req): JsonResponse
    {
        return response()->json(Discount::where('vendor_uuid', $req->user()->uuid)->latest()->paginate($req->per_page ?? 50));
    }

    public static function createDiscount(Request $req): JsonResponse
    {
        $temp = '';
        if (!$req->code)
            $temp = fake()->word();
        else if (count(Discount::where('code', $req->code)->get()))
            return response()->json('کد تخفیفی با این کد از قبل وجود دارد.', 403);
        $discount = Discount::create([
            'vendor_uuid' => $req->user()->uuid,
            'type' => $req->type,
            'code' => $req->code ?? $temp,
            'amount' => $req->amount ?? null,
            'include_shipping' => $req->include_shipping === 1 ? 1 : 0,
            'from' => $req->from ?? null,
            'until' => $req->until ?? null,
            'desc' => $req->desc ?? null,
        ]);
        if ($discount)
            return response()->json('کد تخفیف با موفقیت ایجاد شد.');
        else
            return response()->json('ایجاد کد تخفیف با خطا مواجه شد.', 500);
    }

    public static function deleteDiscount(Request $req): JsonResponse
    {
        $discounts = [];
        if ($req->id)
            $discounts = Discount::where('vendor_uuid', $req->user()->uuid)->where('id', $req->id)->get();
        if (count($discounts)) {
            foreach ($discounts as $d)
                $d->delete();
            return response()->json('کد تخفیف با موفقیت حذف شد.');
        } else
            return response()->json('کد تخفیفی با این شناسه برای این فروشگاه وجود ندارد.', 404);
    }
}
