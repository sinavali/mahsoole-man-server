<?php

namespace App\Models\Products;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Plank\Mediable\Facades\MediaUploader;
use Plank\Mediable\Mediable;

class Category extends Model
{
    use Mediable;

    protected $fillable = [
        'title',
        'vendor_uuid'
    ];
    public static function getCategory($id)
    {
        if (!$id)
            return false;
        $category = self::where('id', $id)->get();
        if (count($category))
            return $category[0];
        return false;
    }
    public static function getCategories($ids)
    {
        if (!count($ids))
            return false;
        $categories = self::whereIn('id', $ids)->get();
        if (count($categories))
            return $categories;
        return false;
    }
    public static function getCategories2($take = null)
    {
        $categories = self::select('id', 'title')->latest();
        if ($take)
            $categories = $categories->take($take)->get();
        else
            $categories = $categories->get();
        $categories = self::proccessCategories($categories);
        if (count($categories))
            return $categories;
        return false;
    }
    public static function getCategoriesLayout($vendor_uuid)
    {
        $categories = self::select('id', 'title');
        if ($vendor_uuid) {
            $categories = $categories->where('vendor_uuid', $vendor_uuid)->get();
            $categories = self::proccessCategories($categories);
            return $categories;
        }
        $categories = self::proccessCategories($categories->latest()->take(7)->get());
        return $categories;
    }

    public static function proccessCategories($categories)
    {
        if (count($categories)) {
            // featured image
            foreach ($categories as $category) {
                $files = $category->getMedia('featured_image')->first();
                $category['featured_image'] = ($files ? $files->getDiskPath() : null);
                //
                $category->makeHidden('categories');
                $category->makeHidden('media');
                if ($category->categories)
                    $category->categories->makeHidden('pivot');
            }
            return $categories;
        }
        return false;
    }
    public static function editCategory($id, $title)
    {
        if (!$id || !$title)
            return false;
        $category = self::where('id', $id)->get();
        if (!count($category))
            return false;
        $category = $category[0];
        if ($category->update(['title' => $title]))
            return self::getCategory($id);
        return false;
    }
    public static function newCategory($req)
    {
        if (!$req->title)
            return false;
        return self::create(['title' => $req->title, 'vendor_uuid' => $req->user()->uuid]);
    }
    public static function assignImage($req, $category)
    {
        if ($req->file('featured_image')) {
            $date = new Carbon();
            $media = MediaUploader::fromSource($req->file('featured_image'))
                ->useFilename('category-image-' . $category->vendor_uuid . '-' . $category->uuid . (microtime(true) * 10000) . mt_rand(1000, 9999))
                ->toDestination('uploads', 'category/' . $date->year . '/' . $date->month . '/' . $date->day)->makePublic()->upload();
            $category->attachMedia($media, 'featured_image');
            return true;
        }
        return false;
    }
    public static function deleteImage($category)
    {
        if ($category) {
            $featuredImages = $category->getMedia('featured_image');
            if (count($featuredImages))
                foreach ($featuredImages as $f)
                    $f->delete();
            if (!count($category->getMedia('featured_image')))
                return true;
            else
                return $category->getMedia('featured_image');
        }
        return false;
    }
    public static function deleteCategories($req)
    {
        if (!$req->ids)
            return false;
        if (self::whereIn('id', explode(',', $req->ids))->delete())
            return true;
        return false;
    }
    // relations
    public function categories()
    {
        return $this->belongsToMany(Product::class, 'category_product', 'product_uuid', 'id');
    }
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'category_product',
            'category_id',
            'product_uuid',
            'id',
            'id',
        );
    }
}