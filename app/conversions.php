<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class conversions extends Model
{
    //
    protected $table='conversions';

    public $timestamps = false;

    protected $fillable  = ['carrier','status','tracking','shipmentLink'];
}
