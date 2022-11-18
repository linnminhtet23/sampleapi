<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleRecord extends Model
{
    use HasFactory;

    public function singleSales()
    {
        return $this->hasMany(SingleSale::class, 'sale_record_id');
    }

    public function credits()
    {
        return $this->hasMany(Credit::class, 'sale_record_id');
    }
}
