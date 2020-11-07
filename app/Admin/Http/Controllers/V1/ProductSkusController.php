<?php

namespace App\Admin\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\ProductSku;

class ProductSkusController extends Controller
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
