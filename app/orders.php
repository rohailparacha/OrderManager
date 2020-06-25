<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\order_details;
use App\returns;
use App\cancelled_orders;


class orders extends Model
{
    // 
    protected $table = 'orders';
    public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['id','account_id','orderId','date','marketplace','storeName','sellOrderId','buyerName','quantity','totalAmount','address1','address2','address3','city', 'state', 'country', 'postalCode','phone','status','poTotalAmount','poNumber','assigned','uid','carrierName','trackingNumber','newTrackingNumber','converted'];


    
    public function orderDetails()
    {
        return $this->hasMany(order_details::class, 'order_id');
    }


    public function returned()
    {
        return $this->hasMany(returns::class, 'order_id');
    }


    public function cancelled()
    {
        return $this->hasMany(cancelled_orders::class, 'order_id', 'id');
    }


}
