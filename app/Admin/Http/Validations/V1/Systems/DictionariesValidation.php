<?php

namespace App\Admin\Http\Validations\V1\Systems;

use App\Models\Systems\SysDictionary;

class DictionariesValidation
{
    public function store()
    {
        return [
            'rules' => [
                'lavel'       => 'required|string',
                'value'       => 'required',
                'sorted'      => 'required|integer',
                'is_disabled' => 'required|boolean',
                'is_default'  => 'required|boolean',
                'type_id'     => 'required|exists:' . (new SysDictionary)->getTable() . ',id',
            ]
        ];
    }

    public function update()
    {
        return [
            'rules' => [
                'lavel'       => 'required|string',
                'value'       => 'required',
                'sorted'      => 'required|integer',
                'is_disabled' => 'required|boolean',
                'is_default'  => 'required|boolean',
            ]
        ];
    }
}
