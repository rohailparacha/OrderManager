<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class new_logs extends Model
{
    //
    protected $table = 'new_logs';
    public $timestamps = false;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['id','date_started','date_completed','upload_link','export_link','dup_link','action','status'];
}
