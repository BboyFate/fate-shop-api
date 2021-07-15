<?php

namespace App\Admin\Http\Controllers\V1\Products;

use Illuminate\Http\Request;
use App\Models\Products\Product;

class ProductsController extends CommonProductsController
{
    public function getProductType()
    {
        return Product::TYPE_NORMAL;
    }

    public function customForm(Request $request)
    {
        return [];
    }
}
