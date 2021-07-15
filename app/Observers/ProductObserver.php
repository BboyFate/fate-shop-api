<?php


namespace App\Observers;

use App\Models\Products\Product;
use App\Jobs\SyncOneProductToEs;

class ProductObserver
{
    public function saved(Product $product)
    {
        // 新增或更新产品，都同步更新一下 Elasticsearch
        dispatch(new SyncOneProductToEs($product));
    }
}
