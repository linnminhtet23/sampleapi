<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'region_id', 'sale_price', 'shop_id'];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}
