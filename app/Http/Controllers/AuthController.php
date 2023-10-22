<?php

namespace App\Http\Controllers;

use App\Methods;
use App\Models\Users\Admin\Admin;
use App\Models\Users\Operator\Operator;
use App\Models\Users\User\User;
use App\Models\Users\UserMeta;
use App\Models\Users\Vendor\Vendor;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tzsk\Sms\Facades\Sms;

// use Tzsk\Sms\Sms;

class AuthController extends Controller
{
    public static function customerAuth(Request $req): JsonResponse
    {
        if (Methods::validateMobile($req->mobile))
            $req->mobile = Methods::mobileToMinimal($req->mobile);
        else
            return response()->json('شماره تماس را در قالب درست وارد کنید.', 400);
        //
        $user = User::where('mobile', $req->mobile)->get();
        //
        if (!count($user))
            $user = User::create(["mobile" => $req->mobile])->assignRole('customer');
        //
        return self::sendOTP($user);
    }

    public static function sendOTP($user, $metaName = null): JsonResponse
    {
        $otp = UserMeta::createOTP($user->uuid);
        $temp = null;
        // demo
        return response()->json("کد احراز هویت ارسال شد.");
        if ($metaName) {
            $supportMobile = self::findVendorSupportMobile($user->metas, $metaName);
            if ($supportMobile)
                $temp = Sms::send($otp)->to(['0' . $supportMobile])->dispatch();
            else
                return response()->json('شماره تماس پشتیبانی ایی وارد نشده است.', 404);
        } else
            $temp = Sms::send($otp)->to(['0' . $user->mobile])->dispatch();
        $temp = json_decode($temp, true);
        if ($temp['StrRetStatus'] !== "Ok")
            return response()->json("سیستم ارسال کد احراز در حال حاظر با مشکل رو به رو میباشد. لطفا بعدا امتحان فرمایید.", 500);
        return response()->json("کد احراز هویت ارسال شد.");
    }

    public static function checkOTP(Request $req)
    {
        // validate request
        if (!$req->mobile)
            return response()->json('شماره موبایل را ارسال کنید.', 429);
        if (Methods::validateMobile($req->mobile))
            $req->mobile = Methods::mobileToMinimal($req->mobile);
        else
            return response()->json('شماره تماس اشتباه است.', 403);
        if (!$req->otp)
            return response()->json('کد احراز را ارسال کنید.', 429);
        if (!$req->action)
            return response()->json('نوع عملیات را ارسال کنید.', 429);
        if (!$req->as)
            return response()->json('نوع کاربر را ارسال کنید.', 429);
        //
        // first i'm checking if it is any otp related to this number
        // so if FALSE don't fetch vendor data for no reason
        // next i'm fetching Vendors data
        if ($req->as == 'vendor') {
            $otp = Vendor::vendorCheckMobileOtp($req->mobile, $req->otp);
            if (!$otp)
                return response()->json("کد احراز یافت نشد. لطفا دوباره امتحان کنید.", 404);
            // now we know there is an OTP with this user and this OTP code
            // and by the time its valid so we delete all 
            // users (every user) otp login thats not valid (its safe)
            UserMeta::deleteStaleLoginOTPsWith($otp->id);
            $vendor = Vendor::getVendor($req->mobile);
            // return response()->json(Vendor::isActive($vendor), 200);
            if (!Vendor::isActive($vendor))
                return response()->json('این فروشگاه فعال نیست. لطفا با پشتیبانی تماس بگیرید.', 503);
            return Vendor::generateTokenForVendor($vendor, $req->action);
        }
        if ($req->as == 'customer') {
            $otp = User::getLatestLoginOtp($req->mobile, $req->otp);
            if (!$otp)
                return response()->json("کد احراز یافت نشد. لطفا دوباره امتحان کنید.", 404);
            // now we know there is an OTP with this user and this OTP code
            // and by the time its valid so we delete all 
            // users (every user) otp login thats not valid (its safe)
            UserMeta::deleteStaleLoginOTPsWith($otp->id);
            return User::generateTokenForCustomer($req->mobile, $req->action);
        } else
            return response()->json('خطایی رخ داده است. لطفا با پشتیبانی تماس بگیرید.', 500);
    }

    public static function logout(Request $req)
    {
        UserMeta::deleteStaleLoginOTPsWith();
        if (!$req->user())
            return response()->json(false);
        $req->user()->tokens()->delete();
        return response()->json('با موفقیت خارج شدید.');
    }

    public static function vendorSignup(Request $req): JsonResponse
    {
        if ($req->owner_name)
            return response()->json('عنوان فروشگاه را وارد کنید.', 429);
        if ($req->owner_last_name)
            return response()->json('عنوان فروشگاه را وارد کنید.', 429);
        if ($req->owner_mobile)
            return response()->json('عنوان فروشگاه را وارد کنید.', 429);
        if ($req->title)
            return response()->json('عنوان فروشگاه را وارد کنید.', 429);
        if ($req->slug)
            return response()->json('نامک فروشگاه را وارد کنید.', 429);
        if ($req->address)
            return response()->json('آدرس فیزیکی فروشگاه را وارد کنید.', 429);
        if ($req->merchant_code)
            return response()->json('مرچنت کد آی دی پی را وارد کنید.', 429);
        //
        // model
        $vendor = Vendor::create(['slug' => $req->slug, 'mobile' => $req->owner_mobile]);
        // metas
        $metas = [];
        $metas[] = ['relation_uuid' => $vendor->uuid, 'meta_key' => 'owner_name', 'meta_value' => $req->owner_name];
        $metas[] = ['relation_uuid' => $vendor->uuid, 'meta_key' => 'owner_last_name', 'meta_value' => $req->owner_last_name];
        $metas[] = ['relation_uuid' => $vendor->uuid, 'meta_key' => 'title', 'meta_value' => $req->title];
        $metas[] = ['relation_uuid' => $vendor->uuid, 'meta_key' => 'address', 'meta_value' => $req->address];
        $metas[] = ['relation_uuid' => $vendor->uuid, 'meta_key' => 'merchant_code', 'meta_value' => $req->merchant_code];
        $req->support_title ? ($metas[] = ['relation_uuid' => $vendor->uuid, 'meta_key' => 'vendor_support_title', 'meta_value' => $req->support_title]) : 0;
        $req->support_mobile ? ($metas[] = ['relation_uuid' => $vendor->uuid, 'meta_key' => 'vendor_support_mobile', 'meta_value' => $req->support_mobile]) : 0;
        $req->state ? ($metas[] = ['relation_uuid' => $vendor->uuid, 'meta_key' => 'vendor_state', 'meta_value' => $req->state]) : 0;
        $req->city ? ($metas[] = ['relation_uuid' => $vendor->uuid, 'meta_key' => 'vendor_city', 'meta_value' => $req->city]) : 0;
        UserMeta::createMany($metas);
        return response()->json('فروشگاه شما ایجاد شد. لطفا منتظر بمانید تا همکاران ما اطلاعات شما را تایید کنند.');
    }

    public static function vendorMobileSendOtp(Request $req): JsonResponse
    {
        if (Methods::validateMobile($req->mobile))
            $req->mobile = Methods::mobileToMinimal($req->mobile);
        else
            return response()->json('شماره تماس اشتباه است', 403);
        $vendors = null;
        if ($req->support) {
            $vendors = Vendor::where('uuid', $req->user()->uuid)->with('metas')->get();
            $vendor = $vendors[0];
            return self::sendOTP($vendor, 'vendor_support_mobile');
        } else {
            $vendor = Vendor::where('mobile', $req->mobile)->get();
            if (count($vendor))
                return self::sendOTP($vendor[0]);
            return response()->json('فروشگاهی با این شماره تماس پیدا نشد. لطفا ثبت نام کنید.', 404);
        }
    }
    public static function customerMobileSendOtp(Request $req): JsonResponse
    {
        if (Methods::validateMobile($req->mobile))
            $req->mobile = Methods::mobileToMinimal($req->mobile);
        else
            return response()->json('شماره تماس اشتباه است', 403);
        $customer = User::getCustomer($req->mobile);
        if ($customer)
            return self::sendOTP($customer);
        return response()->json('کاربری با این شماره تماس پیدا نشد. لطفا ثبت نام کنید.', 404);
    }
    public static function findVendorSupportMobile($metas, $key): string
    {
        foreach ($metas as $m) {
            if ($m['meta_key'] === $key)
                return $m['meta_value'];
        }
        return '';
    }
}