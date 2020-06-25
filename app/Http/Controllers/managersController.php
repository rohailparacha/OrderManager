<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class managersController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');

    }
       
    public function index()
    {
        $managers = User::where('role',2)->get();
        $operators = User::where('role',3)->select()->paginate(500);
        $managerArr = array(); 
        foreach($managers as $manager)
        {
            $managerArr[$manager->id] = $manager->name; 
        }

        return view ('managers',compact('managers','operators','managerArr'));
    }
    
    public function assignOperators(Request $request)
    {
        $rows  = $request->rows; 
        $userId = $request->user;
        
        foreach($rows as $order)
        {
            $id = explode('-',$order)[1];
            $uId = $userId;
            if($id =='all')
                continue;
            $upd = User::where('id',$id)->update(['manager_id'=>$uId]);
        }
        return "success";
    }
}
