<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class transactions extends Model
{
    //
    protected $table='transactions';

    public $timestamps = false; 
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
     protected $fillable= ['bank_id','category_id','date','description','debitAmount','creditAmount'];
}
