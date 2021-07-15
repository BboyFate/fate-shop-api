<?php

namespace App\Admin\Http\Validations\V1\Systems;

use App\Models\Systems\SysDictionaryType;

class DictionaryTypesValidation
{
    public function store()
    {
        return [
            'rules' => [
                'name'        => 'required|string',
                'type'        => 'required|string|unique:' . (new SysDictionaryType)->getTable(),
                'is_disabled' => 'required|boolean',
            ]
        ];
    }

    public function update()
    {
        return [
            'rules' => [
                'name'        => 'required|string',
                'type'        => 'required|string|unique:' . (new SysDictionaryType)->getTable() . ',name,' . request()->route('dictionaryTypeId'),
                'is_disabled' => 'required|boolean',
            ]
        ];
    }
}
