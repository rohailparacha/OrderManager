<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ebay_products extends Model
{
    //
    protected $table='ebay_products';

    public $timestamps = false;

    protected $fillable  = ['created_at','sku','name','productIdType','productId','description','brand','primaryImg','secondaryImg','ebayPrice','category_id','strategy_id','account_id','price'];
}
