<?php

namespace App\Models\Users\User;

use App\Models\Logs\Log;
use App\Models\Users\UserMeta;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
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
    // methods
    public static function getCustomer($mobile)
    {
        $temp = self::select('id', 'uuid', 'mobile', 'email')
            ->where('mobile', $mobile)
            ->with('metas', function ($query) {
                $query->whereIn('meta_key', [
                    'customer_first_name',
                    'customer_last_name',
                ]);
            })->get();
        if (!count($temp))
            return false;
        $temp[0]->makeHidden('id');
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
        if (!count($temp) || !count($temp[0]->metas))
            return false;
        else
            return $temp[0]->metas[0];
    }
    public static function generateTokenForCustomer($mobile, $action)
    {
        $customer = self::getCustomer($mobile);
        $data = [];
        if ($action == 'login')
            $data = [
                "token" => $customer->createToken($customer->uuid . '-' . $mobile)->plainTextToken,
                "user" => $customer,
            ];
        else
            $data = false;
        if ($data)
            return response()->json($data);
        else
            return response()->json('نوع عملیات را ارسال کنید.', 422);
    }
    // relations
    public function metas(): HasMany
    {
        return $this->hasMany(UserMeta::class, 'relation_uuid', 'uuid');
    }
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'by', 'uuid');
    }
}