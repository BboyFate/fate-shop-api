<?php

namespace App\Models\Products;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Model;

class Product extends Model
{
    use SoftDeletes;

    const TYPE_NORMAL       = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL      = 'seckill';

    public static $typeMap = [
        self::TYPE_NORMAL       => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品',
        self::TYPE_SECKILL      => '秒杀商品',
    ];

    protected $fillable = [
        'type',
        'title',
        'long_title',
        'number',
        'image',
        'banners',
        'on_sale',
        'is_free_shipping',
        'rating',
        'sold_count',
        'review_count',
        'price',
    ];

    protected $casts = [
        'on_sale'          => 'boolean',
        'is_free_shipping' => 'boolean',
        'banners'          => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Product $product) {
            $product->skus()->delete();
        });
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function description()
    {
        return $this->hasOne(ProductDescription::class);
    }

    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function properties()
    {
        return $this->hasMany(ProductProperty::class);
    }

    public function crowdfunding()
    {
        return $this->hasOne(CrowdfundingProduct::class);
    }

    public function seckill()
    {
        return $this->hasOne(SeckillProduct::class);
    }

    public function orderItems()
    {
        return $this->hasMany(\App\Models\Orders\OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Orders\OrderItemReview::class);
    }

    public function expressFee()
    {
        return $this->belongsTo(\App\Models\Expresses\ExpressFee::class);
    }

    /**
     * 访问器
     * 商品属性名称分组，属性值集合一起
     * @return mixed
     */
    public function getGroupedPropertiesAttribute()
    {
        return $this->properties
            ->groupBy('name')
            ->map(function ($properties) {
                return $properties->pluck('value')->all();
            });
    }

    /**
     * Scope
     * 根据 ID 获取对应商品并保持次序
     *
     * @param $query
     * @param $ids
     * @return mixed
     */
    public function scopeByIds($query, $ids)
    {
        return $query->whereIn('id', $ids)->orderByRaw(sprintf("FIND_IN_SET(id, '%s')", join(',', $ids)));
    }

    public function toESArray()
    {
        // 只取出需要的字段
        $arr = Arr::only($this->toArray(), [
            'id',
            'category_id',
            'type',
            'title',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]);

        // 如果商品有类目，则 category 字段为类目名数组，否则为空字符串
        $arr['category'] = $this->category ? explode(' - ', $this->category->full_name) : '';
        // 类目的 path 字段
        $arr['category_path'] = $this->category ? $this->category->path : '';
        // strip_tags 函数可以将 html 标签去除
        $arr['description'] = strip_tags($this->description ? $this->description->description : '');
        // 只取出需要的 SKU 字段
        $arr['skus'] = $this->skus->map(function (ProductSku $sku) {
            return Arr::only($sku->toArray(), ['name', 'price']);
        });
        // 只取出需要的商品属性字段
        $arr['properties'] = $this->properties->map(function (ProductProperty $property) {
            return array_merge(Arr::only($property->toArray(), ['name', 'value']), [
                'search_value' => $property->name . ':' . $property->value
            ]);
        });
        return $arr;
    }
}
