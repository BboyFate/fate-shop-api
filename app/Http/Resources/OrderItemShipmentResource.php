<?php

namespace App\Http\Resources;

class OrderItemShipmentResource extends BaseResource
{
    public function toArray($request)
    {
        $data = [
            'id'             => $this->id,
            'shipment_state' => $this->shipment_state,
            'express_no'     => $this->express_no,
            'express_data'   => $this->express_data,
            'readied_at'     => (string)$this->readied_at,
            'delivered_at'   => (string)$this->delivered_at,
            'received_at'    => (string)$this->received_at,
            'canceled_at'    => (string)$this->canceled_at,
        ];

        return $this->filterFields($data);
    }
}
