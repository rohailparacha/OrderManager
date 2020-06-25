<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class bank_accounts extends Model
{
    //
    protected $table = 'bank_accounts';

    public $timestamps = false; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
