<?php

namespace App\Imports;

use App\products;
use App\blacklist;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection
{
    public $data;
    public $rows;
    public function collection(Collection $rows)
    {
        $this->rows = $rows;
        $dataArray = array(); 
        $asinIndex = $this->getIndex('asin');
        $titleIndex = $this->getIndex('title');
        $brandIndex = $this->getIndex('brand');
        $descriptionIndex = $this->getIndex('description');
        $imageIndex = $this->getIndex('large_image');
        $image2Index = $this->getIndex('additional_image_2');
        $priceIndex = $this->getIndex('lowest_new_price_fba');
        $storeIndex = $this->getIndex('store');
        $idIndex = $this->getIndex('product_id');
        $typeIndex = $this->getIndex('product_id_type');
        
        if($asinIndex==-1 || $titleIndex==-1 || $brandIndex==-1 || $descriptionIndex==-1 || $imageIndex==-1 || $image2Index==-1 ||
        $priceIndex==-1 || $storeIndex==-1 || $idIndex==-1 || $typeIndex==-1)
            return $dataArray;

        $count=0;
        foreach ($rows as $row) 
        {
            if($count==0)
            {
                $count++;
                continue; 
            }   
            
            if(count($row)<10)
                continue; 
            else
            {
                
                $asin = $row[$asinIndex];        
                        
                if(strlen($asin)<10)
                {
                    $rem = 10 - strlen($asin);
                    for($a=1; $a<=$rem; $a++)
                    {
                        $asin = "0".$asin;
                    }
                }        
                
                $title = $row[$titleIndex];      
                $brand = $row[$brandIndex];      
                $description = $row[$descriptionIndex];   
                $image1 = $row[$imageIndex];  
                $image2 = $row[$image2Index];        
                $lowestPrice = $row[$priceIndex];    
                $store = $row[$storeIndex];       
                $id = $row[$idIndex];     
                $type = $row[$typeIndex];                       

                if(empty($asin) || empty($title)|| empty($brand)|| empty($description)|| empty($image1)|| empty($type)|| empty($lowestPrice)|| empty($store)|| empty($id))
                    continue;
                
                $count = products::where('asin',$asin)->get()->count();
                
                if($count>0)
                    continue; 

                $count = blacklist::where('sku',$asin)->get()->count();
                
                if($count>0)
                    continue; 

                $dataArray[]= ['asin'=>$asin, 'title'=>$title, 'brand'=>$brand, 'description'=>$description, 'image1'=>$image1, 'image2'=>$image2, 'lowestPrice'=>$lowestPrice, 'store'=>$store, 'id'=>$id, 'type'=>$type ];
                
            } 
        }
        
        $this->data = collect($dataArray);
    }

    public function getIndex($key)
    {
        $rows = $this->rows;
        $counter = 0; 
        foreach ($rows[0] as $col)
        {
            if(strtolower(trim($col)) == $key)
                return $counter; 
            $counter++;
        }

        return -1;
    }
}
