<?php

namespace App\Admin\Http\Validations\V1\Products;

use Illuminate\Support\Arr;
use App\Models\Expresses\ExpressCompany;

class ExpressCompaniesValidation
{
    protected function getRules() {
        return [
            'sorted'     => 'required|integer',
            'is_default' => 'required|boolean',
            'is_showed'  => 'required|boolean',
        ];
    }

    public function store()
    {
        return [
            'rules' => Arr::collapse([$this->getRules(), [
                'name' => 'required|string|unique:'.(new ExpressCompany)->getTable(),
            ]])
        ];
    }

    public function update()
    {
        return [
            'rules' => Arr::collapse([$this->getRules(), [
                'name' => 'required|string|unique:'.(new ExpressCompany)->getTable().',name,' . request()->route('companyId'),
            ]])
        ];
    }
}
