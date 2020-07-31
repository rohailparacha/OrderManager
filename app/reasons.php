<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class reasons extends Model
{
    //
      protected $table = 'reasons';
      public $timestamps = false;
  
      protected $fillable = ['name'];
}
