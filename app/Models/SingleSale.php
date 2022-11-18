<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SingleSale extends Model
{
    use HasFactory;

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function saleRecord()
    {
        return $this->belongsTo(SaleRecord::class, 'sale_record_id');
    }
}
