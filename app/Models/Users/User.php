<?php

namespace App\Models\Users;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Model;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable;

    const REGISTER_WECHAT = 'wechat';
    const REGISTER_WEAPP  = 'weapp';
    const REGISTER_H5     = 'h5';

    public static $registerMap = [
        self::REGISTER_WECHAT => '微信公众号',
        self::REGISTER_WEAPP  => '微信小程序',
        self::REGISTER_H5     => 'H5',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nickname',
        'phone',
        'email',
        'password',
        'avatar',
        'wechat_openid',
        'wechat_unionid',
        'weapp_openid',
        'weapp_session_key',
        'registered_source',
    ];

    protected $dates = [
        'last_actived_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->last_actived_at) {
                $model->last_actived_at = now();
            }
        });
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

    public function favoriteProducts()
    {
        return $this->belongsToMany(\App\Models\Products\Product::class, 'user_favorite_products')
            ->withTimestamps();
            //->orderBy('user_favorite_products.created_at', 'desc');
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function cartItems()
    {
        return $this->hasMany(UserCartItem::class);
    }

    public function socials()
    {
        return $this->hasMany(UserSocial::class);
    }

    public function orderReviews()
    {
        return $this->hasMany(\App\Models\Orders\OrderItemReview::class);
    }
}
