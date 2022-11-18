<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
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
            'name' => $this->name,
            'address' => $this->address,
            'employees' => $this->employees,
            'phone_no_one' => $this->phone_no_one,
            'phone_no_two' => $this->phone_no_two,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
