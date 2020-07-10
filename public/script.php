<?php
$products = array();

$conn = mysqli_connect("localhost","root","","selleractivelive");

if(! $conn ) {
    die('Could not connect: ' . mysqli_error());
 }
 
 //call all functions
 get30Days($conn);
 get60Days($conn);
 get90Days($conn);
 get120Days($conn);
 mysqli_close($conn);

function get30Days($conn)
{

    
    $query = 'select SKU, sum(order_details.quantity) as count from order_details
    left join orders on orders.id = order_details.order_id
    WHERE   date BETWEEN CURDATE() - INTERVAL 30 DAY AND CURDATE()
    group by SKU
    order by count desc';


 
 $result = mysqli_query($conn, $query);
 $prodArray = array(); 
 if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $temp = new stdClass();
        $temp->sku = $row["SKU"];
        $temp->sold = $row["count"];
        $prodArray[] = $temp;
    }
 } 
 updateDatabase($conn, $prodArray, '30days');
}

function get60Days($conn)
{

    
    $query = 'select SKU, sum(order_details.quantity) as count from order_details
    left join orders on orders.id = order_details.order_id
    WHERE   date BETWEEN CURDATE() - INTERVAL 60 DAY AND CURDATE()
    group by SKU
    order by count desc';


 
 $result = mysqli_query($conn, $query);
 $prodArray = array(); 
 if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $temp = new stdClass();
        $temp->sku = $row["SKU"];
        $temp->sold = $row["count"];
        $prodArray[] = $temp;
    }
 } 
 updateDatabase($conn, $prodArray,'60days');
}

function get90Days($conn)
{

    
    $query = 'select SKU, sum(order_details.quantity) as count from order_details
    left join orders on orders.id = order_details.order_id
    WHERE   date BETWEEN CURDATE() - INTERVAL 90 DAY AND CURDATE()
    group by SKU
    order by count desc';


 
 $result = mysqli_query($conn, $query);
 $prodArray = array(); 
 if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $temp = new stdClass();
        $temp->sku = $row["SKU"];
        $temp->sold = $row["count"];
        $prodArray[] = $temp;
    }
 } 
 updateDatabase($conn, $prodArray,'90days');
}

function get120Days($conn)
{

    
    $query = 'select SKU, sum(order_details.quantity) as count from order_details
    left join orders on orders.id = order_details.order_id
    WHERE   date BETWEEN CURDATE() - INTERVAL 120 DAY AND CURDATE()
    group by SKU
    order by count desc';


 
 $result = mysqli_query($conn, $query);
 $prodArray = array(); 
 if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $temp = new stdClass();
        $temp->sku = $row["SKU"];
        $temp->sold = $row["count"];
        $prodArray[] = $temp;
    }
 } 
 updateDatabase($conn, $prodArray,'120days');
}

   
   
function updateDatabase($conn,$data, $var)
{
  foreach($data as $prod)
  {
    $sql = "UPDATE products SET ".$var."= ". $prod->sold."WHERE asin='".$prod->sku."'";
    if ($conn->query($sql) === TRUE) {
      
    } else {
 
    }
  }
  

  
}

