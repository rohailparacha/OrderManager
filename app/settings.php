<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class settings extends Model
{
    //
    protected $table='settings';

    public $timestamps = false;

    protected $fillable  = ['minAmount','maxAmount','amountCheck','stores','storesCheck','discount','maxPrice','minQty','maxQty','quantityRangeCheck','maxDailyOrder','maxDailyAmount','dailyOrderCheck','dailyAmountCheck','priority','name'];
}
