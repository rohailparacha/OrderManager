<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class blacklist extends Model
{
    //
    protected $table = 'blacklist';
    public $timestamps = false;

    protected $fillable = ['date','sku','reason','allowance'];
}
