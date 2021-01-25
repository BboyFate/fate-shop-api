<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderPaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'payment_state'  => $this->payment_state,
            'payment_total'  => $this->payment_total,
            'payment_method' => $this->payment_method,
            'payment_no'     => $this->payment_no,
            'paid_at'        => (string)$this->paid_at,
        ];
    }
}
