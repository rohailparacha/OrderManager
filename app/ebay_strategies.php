<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ebay_strategies extends Model
{
    ////
    protected $table='ebay_strategies';

    public $timestamps = false; 
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
     protected $fillable= ['code','breakeven','type','value','isDefault'];
}
