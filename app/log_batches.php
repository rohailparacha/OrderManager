<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class log_batches extends Model
{
    //
    protected $table = 'log_batches';
    public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['id','name','date_started','date_completed','totalItems','errorItems','successItems','completed','error','status','stage'];
}
