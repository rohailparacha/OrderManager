<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class walmart_products extends Model
{
    //
    protected $table='walmart_products';

    public $timestamps = false;

    protected $fillable  = ['created_at','name','productIdType','productId','seller','link','image','price'];
}
