<?php

namespace App\Models\Users\Operator;

use App\Models\Logs\Log;
use App\Models\Users\UserMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class Operator extends Model
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

    public function metas(): HasMany
    {
        return $this->hasMany(UserMeta::class, 'relation_uuid', 'uuid');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'by', 'uuid');
    }
}
