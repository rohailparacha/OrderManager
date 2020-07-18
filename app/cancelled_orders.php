<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class cancelled_orders extends Model
{
    //        
    protected $table = 'cancelled_orders';
    public $timestamps = false;

    protected $fillable = ['created_at','id','order_id','status'];
}
