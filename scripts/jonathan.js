var discountFactor = '';

function doPost(e) {  
  if(typeof e !== 'undefined')
  {
    var array2d = getOrders_(e);
    
    if (array2d) {
        appendData_(SpreadsheetApp.getActiveSheet(), array2d);
    }
    
    return ContentService.createTextOutput(JSON.stringify("Success"))
  }
  
   return ContentService.createTextOutput(JSON.stringify("Failure"))
}

function doGet(e)
{  
  
  if(e.parameter.function == 'sheetUpdate')
  {
     var flag = updateShipping(e.parameter.sellOrderId,e.parameter.tracking,e.parameter.carrier); 
    
    if(flag)
      return ContentService.createTextOutput(JSON.stringify("Success")).setMimeType(ContentService.MimeType.JSON);
    else
      return ContentService.createTextOutput(JSON.stringify("Failure")).setMimeType(ContentService.MimeType.JSON);
  }
  else
    return ContentService.createTextOutput( updateOrders()).setMimeType(ContentService.MimeType.JSON);
}

function runOnEdit(e) {
    var thisSheet = e.source.getActiveSheet();
    var watchColumns = [25];

    if (watchColumns.indexOf(e.range.columnStart) == -1) return;
    
    if(!e.value)
      e.range.offset(0, 1).setFormula("=K"+e.range.getRow());
    if(e.value.toLowerCase() === "cancel" || e.value.toLowerCase() === "cancelled")
      e.range.offset(0, 1).setValue("0");
    else 
      e.range.offset(0, 1).setFormula("=K"+e.range.getRow());
}

function updateOrders()
{
  var dataArray = [];
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getActiveSheet();
  var dataRange = sheet.getDataRange();
  var values = dataRange.getValues();
  var poTotalAmount=0;
  var poNumber = '';
  var sellOrderId='';
  var tracking='';
  var temp='';
  var itemId='';
  var afpoNumber='';
  
  for (var i = 0; i < values.length; i++) {
        poTotalAmount = values[i][28];
        tracking= values[i][24];
        afpoNumber= values[i][23];
        sellOrderId  = values[i][18];

          temp = tracking.substr(tracking.indexOf('&itemId')+8, tracking.length - tracking.indexOf('&itemId'));
          itemId = temp.substr(0, temp.indexOf('&'));
    
          temp = tracking.substr(tracking.indexOf('&orderId')+9, tracking.length - tracking.indexOf('&orderId'));
          poNumber = temp.substr(0, temp.indexOf('&'));
    
          var record = {};
          record['itemId'] = itemId;
          record['poTotalAmount'] = poTotalAmount;
          record['poNumber'] = poNumber;
          record['afpoNumber'] = afpoNumber;
          record['sellOrderId'] = sellOrderId;    
          dataArray.push(record);
  }
  
  var orders = {};
  orders.data = dataArray;
 
  var result = JSON.stringify(orders);  
  
  var url = "http://ordermanager.supplydistributor.com/api/jonathan_update";
  var options = {
    "method": "post",
    "headers": {
      "Content-Type": "application/json"
    },
    
    "payload": result
  };
  
  var response = UrlFetchApp.fetch(url, options);
  return response;
  
}

function getOrders_(data)
{
  var emails = [];
  var orders = JSON.parse(data.postData.contents).data; 
  
  var itemLink = "";
  var ASIN = "";
  var qty = "";
  var maxPrice = "";
  var itemPrice = "";
  var totalPrice = "";
  var discountPayment = "";
  var name = "";
  var street1 = "";
  var street2 = "";
  var city = "";
  var state = "";
  var zipCode = "";
  var phone = "";
  var storeName = "";
  var referenceNumber = "";
  var dueShip ="";
  var country="";
  
  var date = '';
  
  for(var a=0; a<orders.length; a++)
  {
    itemLink = orders[a].itemLink;
    ASIN = orders[a].ASIN;
    qty = orders[a].qty;
    maxPrice = orders[a].maxPrice;
    itemPrice = orders[a].itemPrice * qty;
   
    discountPayment = orders[a].discountPayment;
    name = orders[a].name;
    PropertiesService.getScriptProperties().setProperty('discountFactor', orders[a].discountFactor);
    street1 = orders[a].street1;
    street2 = orders[a].street2;
    city = orders[a].city;
    state = orders[a].state;
    zipCode = orders[a].zipCode;
    phone = orders[a].phone;
    storeName = orders[a].storeName;
    date = orders[a].date;
    referenceNumber = orders[a].referenceNumber;
    dueShip = orders[a].dueShip;
    country = orders[a].country;
    
    if(findCell(referenceNumber)==0)
      emails.push(['','', '',date,dueShip, ASIN, itemLink, qty,itemPrice, maxPrice, name,street1,street2,city,state,zipCode, country, phone,referenceNumber]);
  }
  
  
  return emails;
}

function findCell(val) {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getActiveSheet();
  var dataRange = sheet.getDataRange();
  var values = dataRange.getValues(); 
  for (var i = 0; i < values.length; i++) {
    var row = "";
      if (values[i][3] == val) {
        row = values[i][3+1];
        return i; 
      }
        
  }

 return 0;  
}

function appendData_(sheet, array2d) {
    if(array2d.length!=0)
      sheet.getRange(sheet.getLastRow() + 1, 1, array2d.length, array2d[0].length).setValues(array2d);
  
    //addFormulas(); 
    //var myvalue = PropertiesService.getScriptProperties().getProperty('discountFactor');
    //addDiscountFactor(myvalue);
    //updateColumn();
}

function addFormulas() {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getActiveSheet();
  var dataRange = sheet.getDataRange();
  var values = dataRange.getValues();

  for (var i = 3; i <= values.length; i++) {
    var cell = sheet.getRange("J"+i);
    cell.setFormula("=SUM(H"+i+":I"+i+")");
        
  }
  
  
}

function addDiscountFactor(factor)
{
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getActiveSheet();
  var dataRange = sheet.getDataRange();
  var values = dataRange.getValues();

  for (var i = 3; i <= values.length; i++) {
    var cell = sheet.getRange("K"+i);
    cell.setFormula("=J"+i+"* (1 - "+factor/100+")");
        
  }
  
  

}


function updateColumn()
{
    var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getActiveSheet();
  var dataRange = sheet.getDataRange();
  var values = dataRange.getValues();
  var flag = false;
  for (var i = 0; i < values.length; i++) {       
         var col = i+1;
         sheet.getRange("Z"+col).setFormula('=K'+col);
  }
  
  SpreadsheetApp.flush()
}

function updateShipping(sellOrderId, trackingNumber, carrier) {
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getActiveSheet();
  var dataRange = sheet.getDataRange();
  var values = dataRange.getValues();
  var flag = false;
  for (var i = 0; i < values.length; i++) {
       
      if (values[i][18] == sellOrderId) {
         var col = i+1;
         sheet.getRange("AE"+col).setValue(trackingNumber);
         sheet.getRange("AF"+col).setValue(carrier);
         flag = true;
         break;
      }
        
  }
  
  SpreadsheetApp.flush()
  
  return flag;
}