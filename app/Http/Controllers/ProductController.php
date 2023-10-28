<?php

namespace App\Http\Controllers;

use App\Models\Products\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public static function getProduct(Request $req)
    {
        if (!$req->uuid)
            return response()->json('شناسه محصول را وارد کنید.', 422);
        //
        $product = Product::getProduct($req->user()->uuid, $req->uuid);
        if (!$product)
            return response()->json('محصولی با این شناسه پیدا نشد.', 404);
        //
        return response()->json($product);
    }
    public static function getProducts(Request $req)
    {
        $conditions = [];
        if ($req->vendor_uuid)
            $conditions[] = ['vendor_uuid', $req->vendor_uuid];
        if ($req->title)
            $conditions[] = ['title', 'like', '%' . $req->title . '%'];
        if ($req->uuid)
            $conditions[] = ['uuid', 'like', '%' . $req->uuid . '%'];
        //
        $products = Product::getProducts($req->user()->uuid, $conditions)->paginate($req->per_page ?? 20);
        $products = Product::proccessProducts($products);
        if ($products)
            return response()->json($products);
        return response()->json('محصولی  پیدا نشد.', 404);
    }
    public static function createProduct(Request $req)
    {
        // return Product::where('uuid', '401614572517')->with('categories')->get();
        // validation
        if (!$req->user()->hasRole('vendor'))
            return response()->json('شما اجازه دسترسی به این بخش را ندارید.', 403);
        if (!$req->title)
            return response()->json('عنوان محصول را وارد کنید.', 422);
        //
        $data = [
            'title' => $req->title,
            'vendor_uuid' => $req->user()->uuid,
        ];
        // Optionals
        if ($req->status)
            $data['status'] = $req->status;
        if ($req->price)
            $data['price'] = $req->price;
        if ($req->off_price)
            $data['off_price'] = $req->off_price;
        if ($req->sku)
            $data['sku'] = $req->sku;
        if ($req->quantity)
            $data['quantity'] = $req->quantity;
        if ($req->content)
            $data['content'] = $req->content;
        //
        $product = Product::create($data);
        $product->makeHidden('id');
        if ($product) {
            if ($req->categories) {
                Product::syncCategories($product, explode(',', $req->categories));
            }
            if ($req->file('featured_image')) {
                $temp = Product::uploadAndAttachFeaturedImage($req->file('featured_image'), $product);
                $temp[0]->attachMedia($temp[1], 'featured_image');
            }
            if ($req->gallery_count) {
                $temp = Product::uploadAndAttachGallery($req, $product);
                for ($i = 0; $i < count($temp[1]); $i++)
                    $temp[0]->attachMedia($temp[1][$i], 'product_gallery');
            }
            // in product edit => remove all featured images after setting new one
            $FI = $product->getMedia('featured_image')->first();
            return response()->json(Product::getProduct($req->user()->uuid, $product->uuid));
        }
        return response()->json('ایجاد محصول با خطا مواجه شد. لطفا دوباره امتحان کنید.', 500);
    }
    public static function editProduct(Request $req)
    {
        // validation
        if (!$req->user()->hasRole('vendor'))
            return response()->json('شما اجازه دسترسی به این بخش را ندارید.', 403);
        if (!$req->uuid)
            return response()->json('شناسه محصول را وارد کنید.', 422);
        if (!$req->title)
            return response()->json('عنوان محصول را وارد کنید.', 422);
        //
        $product = Product::getProduct($req->user()->uuid, $req->uuid);
        if (!$product)
            return response()->json('محصولی با این شناسه پیدا نشد.', 404);
        //
        $data = [
            'title' => $req->title,
            'vendor_uuid' => $req->user()->uuid,
            'active' => 0,
        ];
        // Optionals
        if ($req->status)
            $data['status'] = $req->status;
        if ($req->price)
            $data['price'] = $req->price;
        if ($req->off_price)
            $data['off_price'] = $req->off_price;
        if ($req->sku)
            $data['sku'] = $req->sku;
        if ($req->quantity)
            $data['quantity'] = $req->quantity;
        if ($req->content)
            $data['content'] = $req->content;
        //
        $categories = explode(',', $req->categories);
        if (count($categories) && $categories[0])
            Product::syncCategories($product, $categories);
        else
            Product::syncCategories($product, []);
        if ($req->no_featured_image)
            Product::deleteFeaturedImage($product);
        if ($req->no_gallery_image)
            Product::deleteGallery($product);
        // add medias
        if ($req->file('featured_image')) {
            Product::deleteFeaturedImage($product);
            $temp = Product::uploadAndAttachFeaturedImage($req->file('featured_image'), $product);
            $temp[0]->attachMedia($temp[1], 'featured_image'); // attaching proccess
        }
        if ($req->gallery_count) {
            Product::deleteGallery($product);
            $temp = Product::uploadAndAttachGallery($req, $product);
            for ($i = 0; $i < count($temp[1]); $i++)
                $temp[0]->attachMedia($temp[1][$i], 'product_gallery'); // attaching proccess
        }
        //
        $product = Product::where('uuid', $req->uuid)->get()[0];
        if ($product->update($data)) {
            Product::deactiveProduct($req->uuid);
            return response()->json('محصول با موفقیت ویرایش شد و در صف تایید قرار گرفت.');
        }
        return response()->json('ویرایش محصول با خطا مواجه شد. لطفا دوباره امتحان کنید.', 500);
    }
    public static function deleteProducts(Request $req)
    {
        if (!$req->user()->hasRole('vendor'))
            return response()->json('شما اجازه دسترسی به این بخش را ندارید.', 403);
        if (!$req->uuids)
            return response()->json('شناسه محصولات را ارسال کنید.', 422);
        $res = Product::deleteProducts($req->user()->uuid, $req->uuids);
        if ($res)
            return response()->json('محصولات با موفقیت حذف شدند.');
        return response()->json('حذف محصولات با خطا مواجه شد. لطفا دوباره امتحان کنید.', 500);
    }
}