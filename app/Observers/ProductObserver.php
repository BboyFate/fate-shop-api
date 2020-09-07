<?php


namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function saving(Product $product)
    {
        \Log::info($product);
        \Log::info($product->skus);
    }
}
