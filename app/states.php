<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class states extends Model
{
    //
 //
 protected $table='states';

 public $timestamps = false; 
 
 /**
  * The attributes that are mass assignable.
  *
  * @var array
  */
 
  protected $fillable= ['name','code'];
}
