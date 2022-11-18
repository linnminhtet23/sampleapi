<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyPriceTrack extends Model
{
    use HasFactory;

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}
