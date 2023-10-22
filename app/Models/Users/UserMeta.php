<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserMeta extends Model
{
    protected $fillable = [
        "relation_uuid",
        "meta_key",
        "meta_value",
    ];

    public static function createOTP($uuid ,$action = null)
    {
        $otp = rand(1000, 9999);
        self::create([
            "relation_uuid" => $uuid,
            "meta_key" => $action ? $action : "login_otp",
            "meta_value" => $otp
        ]);
        return $otp;
    }
    public static function deleteStaleLoginOTPsWith($id = null)
    {
        self::where('meta_key', 'login_otp')
            ->where('created_at', '<', Carbon::now()->subMinutes(2))->delete();
        $id ? self::where('id', $id)->delete() : null;
        return true;
    }
}