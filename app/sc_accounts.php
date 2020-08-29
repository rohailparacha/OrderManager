<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class sc_accounts extends Model
{
    //
    protected $table = 'sc_accounts';

    public $timestamps = false;

    protected $fillable = ['token','campaign','name','products'];
}
