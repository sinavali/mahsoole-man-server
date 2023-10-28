<?php

namespace App\Http\Controllers;

use App\Models\Products\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public static function getCategories(Request $req)
    {
        $idArray = [];
        $categories = [];
        if ($req->ids) {
            $idArray = explode(',', $req->ids);
            $categories = Category::getCategories($idArray);
            if ($categories)
                return response()->json($categories);
            return response()->json('دسته بندی ایی یافت نشد.', 404);
        } else
            return response()->json('شناسه دسته بندی ها را ارسال کنید.', 422);
    }
    public static function getAllCategories(Request $req)
    {
        if ($req->paginate) {
            $categories = Category::select('id', 'title', 'created_at')
                ->where('vendor_uuid', $req->user()->uuid)
                ->latest()->paginate($req->per_page ?? 5);
            $categories = Category::proccessCategories($categories);
            return response()->json($categories);
        } else {
            return response()->json(Category::select('id', 'title')->where('vendor_uuid', $req->user()->uuid)->latest()->get());
        }
    }
    public static function assignImage(Request $req)
    {
        if (!$req->id)
            return response()->json('شناسه دسته بندی را ارسال کنید.', 422);
        if (!$req->file('featured_image'))
            return response()->json('فایل را ارسال کنید.', 422);
        $category = Category::getCategory($req->id);
        Category::deleteImage($category);
        Category::assignImage($req, $category);
        if (count($category->getMedia('featured_image')) === 1)
            return response()->json('تصویر دسته بندی با موفقیت تغییر کرد.');
        return response()->json('عملیات با مشکل مواجه شد، لطفا دوباره امتحان کنید.', 500);
    }
    public static function newCategory(Request $req)
    {
        $count = Category::where('vendor_uuid', $req->user()->uuid)->count();
        if ($count > 7)
            return response()->json('حداکثر تعداد مجاز دسته بندی برای هر فروشگاه 7 عدد میباشد.', 503);
        if (!$req->title)
            return response()->json('عنوان دسته بندی را وارد کنید.', 422);
        $category = Category::newCategory($req);
        if ($category)
            return response()->json($category);
        return response()->json('افزودن دسته بندی با خطا مواجه شد. لطفا دوباره امتحان کنید.', 500);
    }
    public static function editCategory(Request $req)
    {
        if (!$req->id)
            return response()->json('شناسه دسته بندی را وارد کنید.', 422);
        if (!$req->title)
            return response()->json('عنوان دسته بندی را وارد کنید.', 422);
        $category = Category::editCategory($req->id, $req->title);
        if ($category)
            return response()->json('دسته بندی با موفقیت ویرایش شد.');
        return response()->json('ویرایش دسته بندی با خطا مواجه شد. لطفا دوباره امتحان کنید.', 500);
    }
    public static function deleteCategories(Request $req)
    {
        if (!$req->ids)
            return response()->json('شناسه دسته بندی ها را وارد کنید.', 422);
        $res = Category::deleteCategories($req);
        if ($res)
            return response()->json('دسته بندی ها با موفقیت حذف شدند.');
        return response()->json('حذف دسته بندی با خطا مواجه شد. لطفا دوباره امتحان کنید.', 500);
    }
}