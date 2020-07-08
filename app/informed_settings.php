<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class informed_settings extends Model
{
    //
    protected $table = 'informed_settings';

    public $timestamps = false;

    protected $fillable = ['minAmount','maxAmount','strategy_id'];
}
