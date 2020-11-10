<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class temp_trackings extends Model
{
    //
    protected $table='temp_trackings';

    public $timestamps = false; 
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
     protected $fillable= ['sellOrderId','trackingLink','status'];
    
}
