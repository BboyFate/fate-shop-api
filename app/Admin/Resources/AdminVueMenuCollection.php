<?php

namespace App\Admin\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Admin\Services\VueMenuService;

class AdminVueMenuCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return (new VueMenuService)->getMenuTree(null, $this->collection);
    }
}
