<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class order_settings extends Model
{
    //
    protected $table = 'order_settings';
    public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['id','price1','price2'];
}
