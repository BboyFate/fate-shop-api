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
        'attributes'
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
     * @param $amount 要减的库存量
     * @return mixed
     * @throws InvalidRequestException
     */
    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InvalidRequestException('减库存不可小于 0', 500);
        }

        return $this->where('id', $this->id)
                    ->where('stock', '>=', $amount)
                    ->decrement('stock', $amount);
    }

    /**
     * 增加库存
     *
     * @param $amount 要增的库存量
     * @return int
     * @throws InvalidRequestException
     */
    public function addStock($amount)
    {
        if ($amount < 0) {
            throw new InvalidRequestException('加库存不可小于 0', 500);
        }

        return $this->increment('stock', $amount);
    }
}
