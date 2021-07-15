<?php

namespace App\Http\Queries;

use Spatie\QueryBuilder\QueryBuilder;
use App\Models\Orders\Order;

class OrderQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(Order::query());

        $this->allowedIncludes('user', 'items.productSku', 'items.product')
            ->defaultSort('-created_at');
    }
}
