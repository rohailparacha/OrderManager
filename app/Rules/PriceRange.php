<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use App\informed_settings;
use DB;


class PriceRange implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $id; 
    protected $acc; 
    public function __construct($id, $acc)
    {
        //
        $this->id = $id; 
        $this->acc = $acc; 
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
        $id = $this->id;
        $acc = $this->acc; 
        if(empty($id))
            $ord = DB::select( DB::raw("SELECT * FROM `informed_settings` WHERE ".$value." between minAmount and maxAmount and account_id =".$acc) );
        else
            $ord = DB::select( DB::raw("SELECT * FROM `informed_settings` WHERE ".$value." between minAmount and maxAmount and account_id =".$acc." and id != ".$id) );
        if(count($ord)>0)
            return false;
        else
            return true; 
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute - the added price is already being used in price range of another setting';
    }
}
