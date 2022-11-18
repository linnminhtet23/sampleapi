<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamageItem extends Model
{
    use HasFactory;

    public function singleBuy()
    {
        return $this->belongsTo(SingleBuy::class, 'single_buy_id');
    }
}
