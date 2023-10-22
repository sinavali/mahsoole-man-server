<?php


namespace App;


use App\Models\Products\Product;
use App\Models\Users\Vendor\Vendor;

class Methods
{
    public static function mobileToMinimal($m)
    {
        // format any phone number to minimal // (+989xxxxxxxxx || 09xxxxxxxxx) -> 9xxxxxxxxx
        if (str_starts_with($m, "+"))
            return substr($m, 3);
        if (str_starts_with($m, "0"))
            return substr($m, 1);
        else
            return $m;
    }

    public static function validateMobile($m): bool
    {
        return preg_match("/^(\+98?)?{?(0?9[0-9]{9}}?)$/", $m) == 1;
    }

    public static function makeProductSlug($t, $v): string
    {
        $titleArray = explode(' ', $t);
        $slug = [];
        foreach ($titleArray as $word)
            $slug[] = trim($word);
        $slug = implode('-', $slug);
        $count = count(Product::where('vendor_uuid', $v)->where('slug', $slug)->get());
        return $count ? $slug . '-' . ($count + 1) : $slug;
    }

    public static function makeVendorSlug($t): string
    {
        $titleArray = explode(' ', $t);
        $slug = [];
        foreach ($titleArray as $word)
            $slug[] = trim($word);
        $slug = implode('-', $slug);
        $count = count(Vendor::where('slug', $slug)->get());
        return $count ? $slug . '-' . ($count + 1) : $slug;
    }

    public static function checkCan($user = null, $permissions = []): bool
    {
        if ($user) {
            $passed = 0;
            foreach ($permissions as $p) {
                foreach ($user->roles as $r) {
                    if ($r->hasPermissionTo($p)) {
                        $passed++;
                        break;
                    }
                }
            }
            if ($passed >= count($permissions))
                return true;
            else
                return false;
        } else
            return true;
    }
}
