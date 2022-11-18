<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'shop_id'];

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'category_id');
    }
}
