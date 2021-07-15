<?php

namespace App\Admin\Http\Validations\V1\Products;

class CrowdfundingProductsValidation extends CommonProductsValidation
{
    public function requestValidation()
    {
        return $this->requestCommonValidation();
    }
}
