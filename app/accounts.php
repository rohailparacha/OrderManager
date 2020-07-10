<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class accounts extends Model
{
    //

    protected $table = 'accounts';

    public $timestamps = false; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['store','username','password','lagTime','manager_id','scaccount_id','informed_id'];
}
