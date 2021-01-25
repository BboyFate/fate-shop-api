<?php

namespace App\Models;

use App\Exceptions\InvalidRequestException;

class ProductSku extends Model
{
    protected $fillable = [
        'name',
        'image',
        'price',
        'stock',
        'attributes',
        'weight',
        'volume',
    ];

    protected $casts = [
        'attributes' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 减库存 返回影响的行数
     *
     * @param $qty 购买的数量
     * @return mixed
     * @throws InvalidRequestException
     */
    public function decreaseStock($qty)
    {
        if ($qty < 0) {
            throw new InvalidRequestException('减库存不可小于 0', 500);
        }

        return $this->where('id', $this->id)
                    ->where('stock', '>=', $qty)
                    ->decrement('stock', $qty);
    }

    /**
     * 增加库存
     *
     * @param $qty 购买的数量
     * @return int
     * @throws InvalidRequestException
     */
    public function addStock($qty)
    {
        if ($qty < 0) {
            throw new InvalidRequestException('加库存不可小于 0', 500);
        }

        return $this->increment('stock', $qty);
    }
}
