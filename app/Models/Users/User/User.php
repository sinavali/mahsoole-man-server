<?php

namespace App\Models\Users\User;

use App\Models\Logs\Log;
use App\Models\Users\UserMeta;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
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

    public static function getUser($mobile)
    {
        $temp = self::select('id', 'uuid', 'mobile')
            ->where('mobile', $mobile)
            ->with('roles')
            ->with('metas', function ($query) {
                $query->whereIn('meta_key', ['name']);
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
    public static function userCheckMobileOtp($mobile, $otp)
    {
        return self::getLatestLoginOtp($mobile, $otp);
    }
    public static function generateTokenForUser($user, $action)
    {
        $data = [];
        if ($action == 'login') {
            $data = [
                "token" => $user->createToken($user->uuid . '-' . $user->mobile)->plainTextToken,
                "user" => $user,
            ];
        } else
            $data = false;
        return response()->json($data ? $data : 'نوع عملیات را ارسال کنید.', $data ? 200 : 429);
    }
    public function metas(): HasMany
    {
        return $this->hasMany(UserMeta::class, 'user_uuid', 'uuid');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'by', 'uuid');
    }
}