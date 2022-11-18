<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyRecord extends Model
{
    use HasFactory;

    public function singleBuys()
    {
        return $this->hasMany(SingleBuy::class, 'buy_record_id');
    }

    public function buyCredits()
    {
        return $this->hasMany(BuyCredit::class, 'buy_record_id');
    }
}
