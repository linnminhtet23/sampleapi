<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BuyResource extends JsonResource
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
            'merchant_name' => $this->merchant_name,
            'whole_total' => $this->whole_total,
            'paid' => $this->paid,
            'credit' => $this->credit,
            'single_buys' => SingleBuyResource::collection($this->singleBuys),
            'buy_credits' => $this->buyCredits,
            'shop_id' => $this->shop_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
