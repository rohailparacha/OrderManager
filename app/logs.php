<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class logs extends Model
{
    // 
    protected $table = 'logs';
    public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['id','date_started','date_completed','identifiers','errorItems','successItems','completed','error','status','stage'];
}
