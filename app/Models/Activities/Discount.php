<?php

namespace App\Models\Activities;

use App\Models\Users\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Discount extends Model
{
    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class, 'uuid', 'vendor_uuid');
    }
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'discount_id', 'id');
    }
}
