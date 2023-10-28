<?php

namespace App\Models\Users\Vendor;

use App\Models\Activities\Discount;
use App\Models\Logs\Log;
use App\Models\Products\Product;
use App\Models\Users\UserMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Vendor extends Model
{
    use HasApiTokens, Notifiable, HasRoles;
    protected $guard_name = "web";

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = strrev(mt_rand(100, 999) . substr(floor(microtime(true) * 10000), 5));
        });
    }

    public static function isActive($vendor)
    {
        return !!$vendor->active;
    }
    public static function getVendor($mobile)
    {
        $temp = self::select('id', 'uuid', 'slug', 'active', 'mobile')
            ->where('mobile', $mobile)
            ->with('roles')
            ->with('metas', function ($query) {
                $query->whereIn('meta_key', [
                    'vendor_owner_first_name',
                    'vendor_owner_last_name',
                    'vendor_owner_mobile',
                    'vendor_shop_name',
                    'vendor_state',
                    'vendor_city',
                    'vendor_address',
                    'vendor_merchant_code',
                    'vendor_support_mobile',
                    'vendor_support_mobile_verified',
                    'vendor_city',
                ]);
            })->get();
        if (!count($temp))
            return false;
        $temp[0]->makeHidden('id');
        $temp[0]->roles->makeHidden(['created_at', 'updated_at', 'pivot', 'guard_name', 'id']);
        $temp[0]->metas->makeHidden(['created_at', 'updated_at', 'relation_uuid']);
        return $temp[0];
    }
    public static function getLatestLoginOtp($mobile, $otp)
    {
        $temp = self::where('mobile', $mobile)
            ->with('metas', function ($query) use ($otp) {
                $query
                    ->where('meta_key', 'login_otp')
                    ->where("meta_value", $otp)
                    ->where('created_at', '>', Carbon::now()->subMinutes(2))
                    ->latest();
            })->get();
        return !count($temp) || !count($temp[0]->metas) ? false : $temp[0]->metas[0];
    }
    public static function vendorCheckMobileOtp($mobile, $otp)
    {
        return self::getLatestLoginOtp($mobile, $otp);
    }
    public static function generateTokenForVendor($vendor, $action)
    {
        $data = [];
        if ($action == 'login') {
            $data = [
                "token" => $vendor->createToken($vendor->uuid . '-' . $vendor->mobile)->plainTextToken,
                "user" => $vendor,
            ];
        } else if ($action == 'validate_support_mobile') {
            // create a meta to validate in another time
            UserMeta::create([
                "relation_uuid" => $vendor->uuid,
                "meta_key" => "verified_vendor_support_mobile",
                "meta_value" => "1"
            ]);
            $data = 'شماره تماس تایید شد.';
        } else if ($action == 'validate_vendor_mobile') {
            // create a meta to validate in another time
            UserMeta::create([
                "relation_uuid" => $vendor->uuid,
                "meta_key" => "verified_vendor_mobile",
                "meta_value" => "1"
            ]);
            $data = 'شماره تماس تایید شد.';
        } else
            $data = false;
        return response()->json($data ? $data : 'نوع عملیات را ارسال کنید.', $data ? 200 : 422);
    }
    public function metas(): HasMany
    {
        return $this->hasMany(UserMeta::class, 'relation_uuid', 'uuid');
    }
    public function metasForProduct(): HasMany
    {
        return $this->hasMany(UserMeta::class, 'relation_uuid', 'uuid')->whereIn('meta_key', [
                  'vendor_shop_name',
                  'vendor_state',
                  'vendor_city',
                  'vendor_address',
                ]);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class, 'vendor_uuid', 'uuid');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'by', 'uuid');
    }
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_uuid', 'uuid');
    }
}