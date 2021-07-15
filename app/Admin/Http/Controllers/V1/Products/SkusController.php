<?php

namespace App\Admin\Http\Controllers\V1\Products;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Products\ProductSku;

class SkusController extends Controller
{
    public function destroy($id)
    {
        $sku = ProductSku::query()->findOrFail($id);
        $sku->delete();

        return $this->response->noContent();
    }

    public function skusDestroy(Request $request)
    {
        $this->validateRequest($request);

        ProductSku::destroy($request->input('sku_ids'));

        return $this->response->noContent();
    }
}
