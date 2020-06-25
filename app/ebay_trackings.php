<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ebay_trackings extends Model
{
    //
    protected $table = 'ebay_trackings';

    public $timestamps = false;

    protected $fillable = ['orderNumber','trackingNumber','carrierName'];
}
