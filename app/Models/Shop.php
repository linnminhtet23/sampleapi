<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class, 'shop_users');
    }

    public function regions()
    {
        return $this->hasMany(Region::class, 'shop_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'shop_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'shop_id');
    }

    public function prices()
    {
        return $this->hasMany(Price::class, 'shop_id');
    }

    public function salePriceTracks()
    {
        return $this->hasMany(SalePriceTrack::class, 'shop_id');
    }

    public function buyPriceTracks()
    {
        return $this->hasMany(BuyPriceTrack::class, 'shop_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'shop_id');
    }

    public function saleRecords()
    {
        return $this->hasMany(SaleRecord::class, 'shop_id');
    }

    public function merchants()
    {
        return $this->hasMany(Merchant::class, 'shop_id');
    }

    public function buyRecords()
    {
        return $this->hasMany(BuyRecord::class, 'shop_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'shop_id');
    }

    public function damageItems()
    {
        return $this->hasMany(DamageItem::class, 'shop_id');
    }
}
