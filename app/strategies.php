<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class strategies extends Model
{
    //
    protected $table='strategies';

    public $timestamps = false; 
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
     protected $fillable= ['code','breakeven','type','value'];
}
