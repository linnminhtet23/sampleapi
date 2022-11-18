<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function prices()
    {
        return $this->hasMany(Price::class, 'region_id');
    }

    public function salePriceTracks()
    {
        return $this->hasMany(SalePriceTrack::class, 'region_id');
    }
}
