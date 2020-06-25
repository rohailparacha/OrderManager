<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class returns extends Model
{
    //
    protected $table = 'returns';

    public $timestamps = false;

    protected $fillable = ['created_at','order_id','sellOrderId','reason','carrier','trackingNumber','label'];
}
