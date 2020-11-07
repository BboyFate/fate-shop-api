<?php

namespace App\Admin\Http\Validations\V1;

class ProductsValidation extends CommonProductsValidation
{
    public function requestValidation()
    {
        return $this->requestCommonValidation();
    }
}
