<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Item([
            'code' => $row['code'],
            'name' => $row['name'],
            'category_id' => $row['category_id'],
            'buy_price' => $row['buy_price'],
            'left_item' => $row['left_item'],
            'shop_id' => $row['shop_id'],
        ]);
    }
}
