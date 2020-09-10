<?php

namespace App\Admin\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\Product;

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
