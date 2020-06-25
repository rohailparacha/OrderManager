<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class gmail_accounts extends Model
{
    //
    
    protected $table = 'gmail_accounts';
    public $timestamps = false;

    protected $fillable = ['email','url','bceurl','accountType'];
}
