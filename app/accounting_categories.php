<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class accounting_categories extends Model
{
    //
    protected $table = 'accounting_categories';

    public $timestamps = false; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type','category'];
}
