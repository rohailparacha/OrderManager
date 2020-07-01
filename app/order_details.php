<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\orders;
use App\products;

class order_details extends Model
{
    // 
    protected $table = 'order_details';
    public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['id','order_id','siteItemId','name','SKU','unitPrice','quantity','totalPrice','shippingPrice'];


    public function order()
    {
        return $this->belongsTo(orders::class);
    }

    public function asin()
    {
        return $this->belongsTo(products::class, 'SKU', 'asin');
    }

}
