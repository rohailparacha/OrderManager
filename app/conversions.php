<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class conversions extends Model
{
    //
    protected $table='conversions';

    public $timestamps = false;

    protected $fillable  = ['order_id','carrier','status','tracking','shipmentLink'];
}
