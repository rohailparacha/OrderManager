<?php

namespace App\Http\Middleware;

use Closure;

class admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $name = $request->route()->getName();
        

        if($name == 'user.create' || $name == 'user.index' || $name == 'user.store' || $name == 'user.update' || $name=='user.edit' || $name=='export')        
        {
            if(auth()->user()->role!=1)
                abort(403);
        }
        
        if($name == 'sync' || $name == 'autoship' || $name=='orderassign')
        {
            if(auth()->user()->role==1 || auth()->user()->role==2)
                return $next($request);
            else
                abort(403);
        }


        if(auth()->user()->role!=1)
        {
            $permissions = json_decode(auth()->user()->assigned_pages);
            
            if($name == 'newOrders')
            {
                if(!empty($permissions) && in_array(1,$permissions))
                    return $next($request);        
                else
                    abort(403);
            }

            elseif($name == 'processedOrders')
            {
                if(!empty($permissions) && in_array(2,$permissions))
                    return $next($request);        
                else
                    abort(403);
            }

            elseif(!empty($permissions) && $name == 'cancelledOrders')
            {
                if(!empty($permissions) && in_array(4,$permissions))
                    return $next($request);        
                else
                    abort(403);
            }

            elseif($name == 'shippedOrders')
            {
                if(!empty($permissions) && in_array(3,$permissions))
                    return $next($request);        
                else
                    abort(403);
            }

            elseif($name == 'conversions')
            {
                if(!empty($permissions) && in_array(5,$permissions))
                    return $next($request);        
                else
                    abort(403);
            }

            elseif($name == 'returns')
            {
                if(!empty($permissions) && in_array(6,$permissions))
                    return $next($request);        
                else
                    abort(403);
            }

            elseif($name == 'products')
            {
                if(!empty($permissions) && in_array(7,$permissions))
                    return $next($request);        
                else
                    abort(403);
            }

            elseif($name == 'ebayProducts')
            {
                if(!empty($permissions) && in_array(8,$permissions))
                    return $next($request);        
                else
                    abort(403);
            }

            elseif($name == 'logs')
            {
                if(!empty($permissions) && in_array(9,$permissions))
                    return $next($request);        
                else
                    abort(403);
            }

            elseif($name == 'report')
            {
                if(!empty($permissions) && in_array(10,$permissions))
                    return $next($request);        
                else
                    abort(403);
            }
            
            
        }
        else
        {
            return $next($request);
        }

            abort(403); 
    }
}
