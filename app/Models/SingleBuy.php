<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SingleBuy extends Model
{
    use HasFactory;

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function buyRecord()
    {
        return $this->belongsTo(BuyRecord::class, 'buy_record_id');
    }
}
