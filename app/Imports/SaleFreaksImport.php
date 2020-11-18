<?php

namespace App\Imports;
use App\orders;
use App\carriers;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SaleFreaksImport implements ToCollection
{
    public $data;    
    public $rows;
    public $flag; 
    public function __construct($flag) {
        $this->flag = $flag;
     
    }
  
    public function collection(Collection $rows)
    {

        $this->rows = $rows;
        $flag = $this->flag; 

        if($flag == '22')
            $accName = 'SaleFreaks1';
    
        if($flag == '23')
            $accName = 'SaleFreaks2';
        
        if($flag == '24')
            $accName = 'SaleFreaks3';  
        
        if($flag == '25')
            $accName = 'SaleFreaks4';
        
        if($flag == '26')
            $accName = 'SaleFreaks5';

        $counter = 0; 
        $poNumber = $this->getIndex('Supplier order number');
        $poAmount = $this->getIndex('Total');
        $tracking = $this->getIndex('Tracking number');
        $carrier = $this->getIndex('Carrier');
        $sellOrderId = $this->getIndex('Market tx id');
        $status = $this->getIndex('Status');
        
        if($poNumber==-1 || $poAmount==-1 || $tracking==-1 || $carrier==-1 || $sellOrderId==-1 || $status ==-1)
            return $counter;
            
        foreach ($rows as $row) 
        {   
            
            if($row[$status]=='Canceled' || $row[$status]=='Error')
            {                
                $update = orders::where('sellOrderId',explode('.',$row[$sellOrderId])[0])->whereIn('flag',['0','16','17','8','9','10','22','23','24','25','26'])->update(['flag'=>'27']);        
            }
            
                if(empty(trim($row[$poNumber])))
                {
                    if($update)
                        $counter++; 
                    continue;
                }

            

            if(empty($row[$carrier])|| empty($row[$tracking]))
            {
                $update = orders::where('sellOrderId',explode('.',$row[$sellOrderId])[0])                
                ->where('status','!=','shipped')
                ->where('status','!=','cancelled')
                ->update([
                'poTotalAmount'=>$row[$poAmount],
                'poNumber'=>$row[$poNumber],        
                'afpoNumber'=>$row[$poNumber],            
                'account_id'=>$accName,        
                'status'=>'processing'
                ]);

            }
            else
            {
                $carrierId = carriers::where('name',$row[$carrier])->get()->first(); 
                                
                if(empty($carrierId))
                {
                    $carrierId = carriers::where('alias','like','%'.$row[$carrier].'%')->get()->first(); 
                }

                if(empty($carrierId))
                    continue;

                $update = orders::where('sellOrderId',explode('.',$row[$sellOrderId])[0])                
                ->where('status','!=','shipped')
                ->where('status','!=','cancelled')
                ->update([
                'poTotalAmount'=>$row[$poAmount],
                'poNumber'=>$row[$poNumber],        
                'afpoNumber'=>$row[$poNumber],  
                'trackingNumber'=>$row[$tracking],  
                'carrierName'=>$carrierId->id,            
                'account_id'=>$accName,        
                'status'=>'processing'
                ]);
            }
            

            if($update)
                $counter++;            
        }
        
        $this->data = $counter;
    }

    public function getIndex($key)
    {
        $rows = $this->rows;
        $counter = 0; 
        foreach ($rows[0] as $col)
        {
            if(strtolower(trim($col)) == strtolower(trim($key)))
                return $counter; 
            $counter++;
        }

        return -1;
    }

}
