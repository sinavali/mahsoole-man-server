<?php

namespace App\Http\Controllers;

use App\Models\Products\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Plank\Mediable\Facades\MediaUploader;

class MediaController extends Controller
{
    public static function attachMedia(Request $req)
    {
        return 'demo';
    }
    public static function uploadMedia(Request $req)
    {
        if ($req->file('featured_image') && Product::uploadFeaturedImage($req->file('featured_image')))
            return true;
        return 'false';
    }
}