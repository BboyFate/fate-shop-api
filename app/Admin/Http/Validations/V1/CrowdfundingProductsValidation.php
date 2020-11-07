<?php

namespace App\Admin\Http\Validations\V1;

class CrowdfundingProductsValidation extends CommonProductsValidation
{
    public function requestValidation()
    {
        return $this->requestCommonValidation();
    }
}
