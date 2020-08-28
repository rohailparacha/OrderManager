<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class amazon_settings extends Model
{
    protected $table = 'amazon_settings';

    public $timestamps = false; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['soldDays','soldQty','createdBefore'];

}
