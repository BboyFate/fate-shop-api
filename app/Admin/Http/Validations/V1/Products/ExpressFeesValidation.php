<?php

namespace App\Admin\Http\Validations\V1\Products;

use Illuminate\Support\Arr;
use App\Models\Expresses\ExpressFee;

class ExpressFeesValidation
{
    protected function getRules() {
        return [
            'name'       => 'required|string',
            'fee_type'   => 'required|in:' . implode(array_keys(ExpressFee::$feeTypeMap), ','),
            'is_default' => 'required|boolean',
        ];
    }

    public function store()
    {
        return [
            'rules' => Arr::collapse([$this->getRules(), [
                'name' => 'required|string|unique:'.(new ExpressFee)->getTable(),
            ]])
        ];
    }

    public function update()
    {
        return [
            'rules' => Arr::collapse([$this->getRules(), [
                'name' => 'required|string|unique:'.(new ExpressFee)->getTable().',name,' . request()->route('feeId'),
            ]])
        ];
    }
}
