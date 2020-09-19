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
    ];

    protected $dates = ['last_actived_at'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

//    public function getFurImageAttribute($field)
//    {
//        return $this->attributes[$field] = config('app.url') . $field;
//    }/**
// * 访问器-头像链接字段
// * @param string $value
// * @return string
// */
//public function getAvatarAttribute($value)
//{
//    if (empty($value)) {
//        return 'https://cdn.learnku.com/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/600/h/60';
//    }
//    return $value;
//}

    public function images($type = AdminImage::TYPE_PRODUCT)
    {
        return $this->hasMany(AdminImage::class)->where('type', $type);
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
