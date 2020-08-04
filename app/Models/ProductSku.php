<?php

namespace App\Models;

use App\Exceptions\InternalException;

class ProductSku extends Model
{
    protected $fillable = ['name', 'description', 'price', 'stock', 'attributes'];

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
     * @param int $amount 库存
     * @return mixed
     * @throws InternalException
     */
    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('减库存不可小于 0');
        }

        return $this->where('id', $this->id)
                    ->where('stock', '>=', $amount)
                    ->decrement('stock', $amount);
    }

    /**
     * 增加库存
     *
     * @param int $amount 库存
     * @return int
     * @throws InternalException
     */
    public function addStock($amount)
    {
        if ($amount < 0) {
            throw new InternalException('加库存不可小于 0');
        }

        return $this->increment('stock', $amount);
    }
}
