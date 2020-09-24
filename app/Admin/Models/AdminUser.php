<?php

namespace App\Admin\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class AdminUser extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'password',
        'nickname',
        'phone',
        'is_disabled',
        'last_actived_at',
    ];

    protected $dates = ['last_actived_at'];

    protected $casts = [
        'is_disabled' => true
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function getAvatarAttribute()
    {
        $avatar = $this->images(AdminImage::TYPE_AVATAR)->latest()->first()->path;
        $uri = config('app.url');
        if (isset($avatar)) {
            return $uri . $avatar;
        }
        return $uri . config('app.image_admin_avatar');
    }

    public function images($type = AdminImage::TYPE_PRODUCT)
    {
        return $this->hasMany(AdminImage::class)->where('type', $type);
    }

    public function roles()
    {
        return $this->morphToMany(
            AdminRole::class,
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        );
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
