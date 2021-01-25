<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    protected $only = [];
    protected $except = [];

    public function setOnly($only)
    {
        $this->only = $only;
        return $this;
    }

    public function setExcept($except)
    {
        $this->except = $except;
        return $this;
    }

    protected function filterFields($data)
    {
        if ($this->only) {
            return collect($data)->only($this->only)->toArray();
        } else if ($this->except) {
            return collect($data)->forget($this->except)->toArray();
        } else {
            return $data;
        }
    }
}
