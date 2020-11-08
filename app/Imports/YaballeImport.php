<?php

namespace App\Imports;
use App\orders;
use App\carriers;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class YaballeImport implements ToCollection
{
    public $data;    
    public $rows;

  
    public function collection(Collection $rows)
    {
        $this->rows = $rows;
        $counter = 0; 
        $poNumber = $this->getIndex('source_order_id');
        $poAmount = $this->getIndex('amount_paid');
        $tracking = $this->getIndex('tracking_number');
        $carrier = $this->getIndex('carrier');
        $sellOrderId = $this->getIndex('transaction_id');

        if($poNumber==-1 || $poAmount==-1 || $tracking==-1 || $carrier==-1 || $sellOrderId==-1)
            return $counter;
            
        
        foreach ($rows as $row) 
        {                          
            if(empty(trim($row[$poNumber])))
                continue;

            if(empty($row[$carrier])|| empty($row[$tracking]))
            {
                $update = orders::where('sellOrderId',explode('.',$row[$sellOrderId])[0])                
                ->where('status','!=','shipped')
                ->update([
                'poTotalAmount'=>$row[$poAmount],
                'poNumber'=>$row[$poNumber],        
                'afpoNumber'=>$row[$poNumber],            
                'account_id'=>'Yaballe',        
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
                ->update([
                'poTotalAmount'=>$row[$poAmount],
                'poNumber'=>$row[$poNumber],        
                'afpoNumber'=>$row[$poNumber],  
                'trackingNumber'=>$row[$tracking],  
                'carrierName'=>$carrierId->id,            
                'account_id'=>'Yaballe',        
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
