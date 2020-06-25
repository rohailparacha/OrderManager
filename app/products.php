<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\order_details;

class products extends Model
{
    //

    protected $table='products';

    public $timestamps = false;

    protected $fillable  = ['image','sc_id','account','asin','upc','title','totalSellers','lowestPrice','price','strategy_id'];


    public function orderDetails()
    {
        return $this->hasMany(order_details::class, 'SKU', 'asin');
    }

}
