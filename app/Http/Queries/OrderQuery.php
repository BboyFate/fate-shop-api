<?php

namespace App\Http\Queries;

use App\Models\Order;
use Spatie\QueryBuilder\QueryBuilder;

class OrderQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(Order::query());

        $this->allowedIncludes('user', 'items.productSku', 'items.product')
            ->defaultSort('-created_at');
    }
}
