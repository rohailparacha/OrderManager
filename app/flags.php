<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class flags extends Model
{
    //
    protected $table = 'flags';
    public $timestamps = false;

    protected $fillable = ['name','color'];
}
