<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class informed_accounts extends Model
{
    //
    protected $table = 'informed_accounts';

    public $timestamps = false;

    protected $fillable = ['name','token'];
}
