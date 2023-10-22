<?php

namespace App\Models\Products;

use App\Models\Products\Category;
use App\Models\Products\CategoryProduct;
use App\Models\Users\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Plank\Mediable\Facades\MediaUploader;
use Plank\Mediable\Mediable;

class Product extends Model
{
    use Mediable;

    protected $fillable = [
        'title',
        'status',
        'vendor_uuid',
        'content',
        'sku',
        'quantity',
        'price',
        'off_price',
    ];
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = strrev(mt_rand(100, 999) . substr(floor(microtime(true) * 10000), 5));
        });
    }
    public static function deactiveProduct($uuid)
    {
        if (!$uuid)
            return false;
        $product = self::where('uuid', $uuid)->get();
        if (count($product)) {
            $product[0]->active = 0;
            if ($product[0]->update())
                return true;
            return false;
        }
        return false;
    }
    public static function productCanGetProccessed($uuid)
    {
        $product = self::where('uuid', $uuid)->get();
        if (!count($product))
            return [false, 'محصول یافت نشد'];
        $product = $product[0];
        if ($product->status !== 'published')
            return [false, 'فروشنده محصول را از دسترس خارج کرده است'];
        if ($product->active !== 1)
            return [false, 'محصول در صف تایید است.'];
        if ($product->quantity < 1)
            return [false, 'موجودی محصول کافی نمی باشد.'];
        return [true, $product];
    }

    public static function activeProduct($uuid)
    {
        if (!$uuid)
            return false;
        $product = self::where('uuid', $uuid)->get();
        if (count($product)) {
            $product[0]->active = 1;
            if ($product[0]->update())
                return true;
            return false;
        }
        return false;
    }
    public static function syncCategories($product, $catIds)
    {
        if (!$product)
            return false;
        if (!count($catIds))
            $catIds = [];
        if ($product->categories()->sync($catIds))
            return true;
        return false;
    }
    public static function getProduct($vendor_uuid, $uuid, $inc = false)
    {
        $product = self::select('id', 'title', 'uuid', 'active', 'status', 'content', 'sku', 'quantity', 'price', 'off_price', 'created_at', 'vendor_uuid')
            ->with(['metas:id,meta_key,meta_value', 'categories:id,title'])->where('vendor_uuid', $vendor_uuid)->where('uuid', $uuid);
        if ($inc)
            return $product;
        else {
            $product = $product->get();
            if (count($product)) {
                // featured image
                $files = $product[0]->getMedia('featured_image')->first();
                $product[0]['featured_image'] = ($files ? $files->getDiskPath() : null);
                // product gallery
                $files = [];
                foreach ($product[0]->getMedia('product_gallery') as $f)
                    $files[] = $f->getDiskPath();
                $product[0]['gallery'] = $files;
                //
                $product[0]->makeHidden('id');
                $product[0]->makeHidden('media');
                $product[0]->categories->makeHidden('pivot');
                // $product[0]->vendor->makeHidden('pivot');
                return $product[0];
            }
        }
        return false;
    }
    public static function getProductByUUID($uuid)
    {
        $product = self::select('id', 'title', 'uuid', 'active', 'status', 'content', 'sku', 'quantity', 'price', 'off_price', 'created_at', 'vendor_uuid')
            ->with(['metas:id,meta_key,meta_value', 'categories:id,title', 'vendor.metasForProduct:relation_uuid,meta_key,meta_value'])->where('uuid', $uuid);

        $product = $product->get();
        if (!count($product))
            return false;
        // featured image
        $files = $product[0]->getMedia('featured_image')->first();
        $product[0]['featured_image'] = ($files ? $files->getDiskPath() : null);
        // product gallery
        $files = [];
        foreach ($product[0]->getMedia('product_gallery') as $f)
            $files[] = $f->getDiskPath();
        $product[0]['gallery'] = $files;
        //
        $product[0]->makeHidden('id');
        $product[0]->makeHidden('media');
        $product[0]->categories->makeHidden('pivot');
        $product[0]->vendor->makeHidden('active');
        $product[0]->vendor->makeHidden('email');
        $product[0]->vendor->makeHidden('mobile');
        $product[0]->vendor->makeHidden('metas');
        $product[0]->vendor->metas->makeHidden('relation_uuid');
        return $product[0];
    }
    public static function uploadAndAttachFeaturedImage($file, $product)
    {
        $date = new Carbon();
        $media = MediaUploader::fromSource($file)
            ->useFilename('product-image-' . $product->vendor_uuid . '-' . $product->uuid . (microtime(true) * 10000) . mt_rand(1000, 9999))
            ->toDestination('uploads', 'product/' . $date->year . '/' . $date->month . '/' . $date->day)->makePublic()->upload();
        return [$product, $media];
    }
    public static function uploadAndAttachGallery($req, $product)
    {
        if ($req->gallery_count) {
            $date = new Carbon();
            $medias = [];
            for ($i = 1; $i <= $req->gallery_count; $i++) {
                $media = MediaUploader::fromSource($req->file(('gallery_' . $i)))
                    ->useFilename('product-gallery-' . $product->vendor_uuid . '-' . $product->uuid . (microtime(true) * 10000) . mt_rand(1000, 9999))
                    ->toDestination('uploads', 'product/' . $date->year . '/' . $date->month . '/' . $date->day)->makePublic()->upload();
                $medias[] = $media;
            }
            return [$product, $medias];
        }
        return false;
    }
    public static function deleteFeaturedImage($product)
    {
        if ($product) {
            $featuredImages = $product->getMedia('featured_image');
            if (count($featuredImages))
                foreach ($featuredImages as $f)
                    $f->delete();
            if (!count($product->getMedia('featured_image')))
                return true;
        }
        return false;
    }
    public static function deleteGallery($product)
    {
        if ($product) {
            $galleryImages = $product->getMedia('product_gallery');
            if (count($galleryImages))
                foreach ($galleryImages as $f)
                    $f->delete();
            if (!count($product->getMedia('product_gallery')))
                return true;
            return $product->getMedia('product_gallery');
        }
        return false;
    }
    public static function deleteProducts($vendor_uuid, $uuids)
    {
        $uuidArray = explode(',', $uuids);
        if (!count($uuidArray))
            return false;
        if (self::whereIn('uuid', $uuidArray)->where('vendor_uuid', $vendor_uuid)->delete())
            return true;
        return false;
    }
    public static function getProducts($vendor_uuid, $conditions = [])
    {
        return self::select('id', 'uuid', 'title', 'active', 'status', 'content', 'sku', 'quantity', 'price', 'off_price', 'created_at', 'vendor_uuid')
            ->with(['metas:id,meta_key,meta_value', 'categories:id,title'])->where('vendor_uuid', $vendor_uuid)->where($conditions)->latest();
    }
    public static function getProducts2($take = null)
    {
        $products = self::select('id', 'uuid', 'title', 'active', 'status', 'content', 'sku', 'quantity', 'price', 'off_price', 'created_at', 'vendor_uuid')
            ->with(['categories:id,title'])->where('status', 'published')->where('active', 1)->latest();
        if ($take)
            $products = $products->take($take)->get();
        else
            $products = $products->get();
        return self::proccessProducts($products);
    }
    public static function getOnSaleProducts($take = null)
    {
        $products = self::select('id', 'uuid', 'title', 'active', 'status', 'content', 'sku', 'quantity', 'price', 'off_price', 'created_at', 'vendor_uuid')
            ->with(['categories:id,title'])->where('status', 'published')->where('active', 1)->where('off_price', "!=", '')->latest();
        if ($take)
            $products = $products->take($take)->get();
        else
            $products = $products->get();
        return self::proccessProducts($products);
    }
    public static function proccessProducts($products)
    {
        if (count($products)) {
            // featured image
            foreach ($products as $product) {
                $files = $product->getMedia('featured_image')->first();
                $product['featured_image'] = ($files ? $files->getDiskPath() : null);
                // product gallery
                $files = [];
                foreach ($product->getMedia('product_gallery') as $f)
                    $files[] = $f->getDiskPath();
                $product['gallery'] = $files;
                //
                $product->makeHidden('id');
                $product->makeHidden('media');
                if ($product->categories)
                    $product->categories->makeHidden('pivot');
            }
            return $products;
        }
        return false;
    }
    // realtions
    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class, 'uuid', 'vendor_uuid')->select('uuid', 'slug', 'mobile', 'email', 'active');
    }

    public function metas(): HasMany
    {
        return $this->hasMany(ProductMeta::class, 'product_uuid', 'uuid')->select('id', 'meta_key', 'meta_value');
    }
    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'category_product',
            'product_uuid',
            'category_id',
            'uuid',
            'id',
        );
    }
}