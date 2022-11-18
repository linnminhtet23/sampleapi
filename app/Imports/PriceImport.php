<?php

namespace App\Imports;

use App\Models\Price;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PriceImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Price([
            'item_id' => $row['item_id'],
            'region_id' => $row['region_id'],
            'sale_price' => $row['sale_price'],
            'shop_id' => $row['shop_id'],
        ]);
    }
}
