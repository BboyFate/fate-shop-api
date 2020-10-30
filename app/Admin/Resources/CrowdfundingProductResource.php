<?php

namespace App\Admin\Resources;

use App\Models\CrowdfundingProduct;
use Illuminate\Http\Resources\Json\JsonResource;

class CrowdfundingProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id'            => $this->id,
            'target_amount' => $this->target_amount,
            'total_amount'  => $this->total_amount,
            'user_count'    => $this->user_count,
            'end_at'        => (string) $this->end_at,
            'status'        => $this->status,
            'status_text'   => CrowdfundingProduct::$statusMap[$this->status],
            'created_at'    => (string) $this->created_at,
            'updated_at'    => (string) $this->updated_at,
        ];

        return $data;
    }
}
