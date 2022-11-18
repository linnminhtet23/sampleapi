<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'purchase_total' => $this->purchase_total,
            'sale_record_total' => $this->sale_record_total,
            'extra_charges' => $this->extra_charges,
            'whole_total' => $this->whole_total,
            'paid' => $this->paid,
            'credit' => $this->credit,
            'single_sales' => SingleSaleResource::collection($this->singleSales),
            'credits' => $this->credits,
            'shop_id' => $this->shop_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
