<?php

namespace App\Admin\Http\Controllers\V1\Products;

use Illuminate\Http\Request;
use App\Models\Products\Product;

class CrowdfundingProductsController extends CommonProductsController
{
    public function getProductType()
    {
        return Product::TYPE_CROWDFUNDING;
    }

    protected function customForm(Request $request)
    {
        return [
            'target_amount' => $request['target_amount'],
            'end_at'        => $request['end_at'],
        ];
    }
}
