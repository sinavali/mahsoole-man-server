<?php

namespace App\Http\Controllers;

use App\Models\Products\Category;
use App\Models\Products\Product;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public static function layout(Request $req)
    {
        $categories = Category::getCategoriesLayout($req->vendor_uuid);
        return response()->json([
            'categories' => $categories,
        ]);
    }
    public static function mainPage()
    {
        $categories = Category::getCategories2(7);
        $latestProducts = Product::getProducts2(4);
        $onSaleProducts = Product::getOnSaleProducts(10);
        $onSaleProducts = Product::getProducts2(10);

        return response()->json([
            'categories' => $categories,
            'latest_products' => $latestProducts,
            'on_sale_products' => $onSaleProducts,
            'banners' => [
                [
                    'title' => 'محبوب هفته',
                    'desc' => 'در دسته بندی بانوان ببینید',
                    'link' => [
                        'title' => 'خرید کنید',
                        'link' => '/cat/ladies',
                    ],
                    'url' => 'banners/banner-1.jpg',
                ],
                [
                    'title' => 'محبوب هفته',
                    'desc' => 'در دسته بندی مردان ببینید',
                    'link' => [
                        'title' => 'خرید کنید',
                        'link' => '/cat/men',
                    ],
                    'url' => 'banners/banner-2.jpg',
                ],
                [
                    'title' => 'محبوب هفته',
                    'desc' => 'در دسته بندی کودکان ببینید',
                    'link' => [
                        'title' => 'خرید کنید',
                        'link' => '/cat/children',
                    ],
                    'url' => 'banners/banner-3.jpg',
                ]
            ]
        ]);
    }

    public static function productPage(Request $req)
    {
        if (!$req->uuid)
            return response()->json('شناسه کالا را ارسال کنید.', 429);
        $product = Product::getProductByUUID($req->uuid);
        return response()->json($product);
    }
}