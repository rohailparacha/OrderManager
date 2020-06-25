<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class carriers extends Model
{
    //
    protected $table = 'carriers';
    public $timestamps = false;

    protected $fillable = ['alias','name'];
}
