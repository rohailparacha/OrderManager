<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('welcome');
})->name('home');



Auth::routes();

Route::group(['middleware' => 'auth'], function () {
	Route::resource('user', 'UserController', ['except' => ['show']])->middleware('admin');
	Route::get('profile', ['as' => 'profile.edit', 'uses' => 'ProfileController@edit']);
	Route::put('profile', ['as' => 'profile.update', 'uses' => 'ProfileController@update']);
	Route::put('profile/password', ['as' => 'profile.password', 'uses' => 'ProfileController@password']);
	Route::get('newOrders','orderController@newOrders')->name('newOrders')->middleware('admin');
	Route::get('/reset/{id}','orderController@reset')->name('reset')->middleware('admin');
	
	// New Order Pages Routes
	Route::get('newOrdersFlagged','orderController@newOrdersFlagged')->name('newOrdersFlagged')->middleware('admin');
	Route::get('newOrdersMultiItems','newOrdersController@newOrdersMultiItems')->name('newOrdersMultiItems')->middleware('admin');
	Route::get('newOrdersPrice1','newOrdersController@newOrdersPrice1')->name('newOrdersPrice1')->middleware('admin');
	Route::get('newOrdersPrice2','newOrdersController@newOrdersPrice2')->name('newOrdersPrice2')->middleware('admin');
	Route::get('newOrdersZero','newOrdersController@newOrdersZero')->name('newOrdersZero')->middleware('admin');
	Route::get('newOrdersMovie','newOrdersController@newOrdersMovie')->name('newOrdersMovie')->middleware('admin');
	Route::get('newOrdersFood','newOrdersController@newOrdersFood')->name('newOrdersFood')->middleware('admin');
	Route::get('newOrdersExpensive','newOrdersController@newOrdersExpensive')->name('newOrdersExpensive')->middleware('admin');
	Route::post('/assignMovie', 'newOrdersController@assignMovie')->middleware('admin');
	Route::post('/assignFood', 'newOrdersController@assignFood')->middleware('admin');	
	Route::get('/pricingSettings', 'newOrdersController@settings')->name('pricingSettings')->middleware('admin');
	Route::post('/pricingSettingsStore', 'newOrdersController@storeSettings')->name('pricingSettingsStore')->middleware('admin');
	Route::get('/orderTemplate', 'newOrdersController@getTemplate')->middleware('admin');
	Route::post('newFilter','newOrdersController@filter')->middleware('admin');	
	Route::post('newSearch','newOrdersController@search')->middleware('admin');	

	Route::get('processedOrders','orderController@processedOrders')->name('processedOrders')->middleware('admin');
	Route::get('dueDateComing','orderController@dueComing')->name('dueComing')->middleware('admin');	
	Route::get('cancelledOrders','orderController@cancelledOrders')->name('cancelledOrders')->middleware('admin');
	Route::get('shippedOrders','orderController@shippedOrders')->name('shippedOrders')->middleware('admin');
	Route::get('conversions','orderController@conversions')->name('conversions')->middleware('admin');
	Route::get('conversions2','orderController@conversions2')->name('conversions2')->middleware('admin');
	Route::get('deliveredConversions','orderController@deliveredConversions')->name('deliveredConversions')->middleware('admin');
	Route::get('conversionssync/{id}','orderController@conversionssync')->name('conversionssync')->middleware('admin');	
	Route::get('upsconversions','orderController@upsConversions')->name('upsConversions')->middleware('admin');	
	Route::get('upsapproval','orderController@upsApproval')->name('upsApproval')->middleware('admin');	
	Route::get('upsshipped','orderController@upsShipped')->name('upsShipped')->middleware('admin');	
	Route::any('upsfilter','orderController@upsfilter')->name('upsfilter')->middleware('admin');
	Route::any('upsexport','orderController@upsexport')->name('upsexport')->middleware('admin');	
	Route::get('lookup','orderController@lookup')->name('lookup')->middleware('admin');
	Route::any('lookupFilter','orderController@filterLookup')->middleware('admin');

	Route::post('getManualBce','orderController@getManualBce')->middleware('admin');
	Route::get('cancelOrder/{id}','orderController@cancelOrder')->name('cancelOrder');	
	Route::get('sync','orderController@syncOrders')->name('sync')->middleware('admin');	
	Route::any('search','orderController@search')->name('search');
	Route::get('orderDetails/{id}','orderController@details')->name('orderDetails');
	Route::post('updateNotes','orderController@updateNotes')->name('updateNotes');
	Route::post('updateOrder','orderController@updateOrder')->name('updateOrder');
	Route::get('assign','orderController@assign')->name('orderassign')->middleware('admin');
	Route::post('assignOrder','orderController@assignOrder')->middleware('admin');
	Route::any('orderexport','orderController@export')->name('orderexport')->middleware('admin');	
	Route::get('test','orderController@newBCE');

	Route::post('autoship','orderController@autoship')->name('autoship')->middleware('admin');
	Route::get('accounts','accountsController@index')->name('accounts')->middleware('admin');
	Route::get('account/create','accountsController@create')->name('createaccount')->middleware('admin');
	Route::post('createaccount','accountsController@store')->name('storeaccount')->middleware('admin');
	Route::post('destroyaccount','accountsController@destroy')->name('destroyaccount')->middleware('admin');
	Route::get('account/{id}/edit','accountsController@edit')->name('editaccount')->middleware('admin');
	Route::post('updateaccount','accountsController@update')->name('updateaccount')->middleware('admin');
	Route::post('checkPass','orderController@checkPass')->name('checkPass');
	Route::post('checkAssignPass','orderController@checkAssignPass')->name('checkAssignPass');
	Route::post('checkResetPass','orderController@checkResetPass')->name('checkResetPass');
	Route::get('report','reportsController@index')->name('report')->middleware('admin');
	/*-------find duplicate-----------------*/
	Route::get('duplicate-record','reportsController@duplicateRecord')->name('duplicate-record')->middleware('admin');

	Route::any('duplicate-search','reportsController@search')->name('duplicate-search')->middleware('admin');

	Route::any('search-filter','reportsController@searchfilter')->name('search-filter')->middleware('admin');


	Route::get('dailyReport','reportsController@dailyReport')->name('dailyReport')->middleware('admin');
	Route::any('filter','reportsController@filter')->name('filter')->middleware('admin');
	Route::any('export','reportsController@export')->name('export')->middleware('admin');	
	Route::get('getAmzDetails','orderController@getAmazonDetails')->middleware('admin');
	Route::get('orderFlag/{id}/{flag}','orderController@orderFlag')->middleware('admin');
	Route::get('orderFlag/{route}/{id}/{flag}','orderController@orderFlagRoute')->middleware('admin');
	Route::get('accTransfer/{id}/{account}','orderController@accTransfer')->middleware('admin');
	Route::get('accTransfer/{route}/{id}/{account}','orderController@accTransferRoute')->middleware('admin');
	Route::any('orderFilter','orderController@filter')->middleware('admin');
	Route::any('orderFilterFlagged','orderController@filterFlagged')->middleware('admin');
	Route::any('orderFilterExpensive','orderController@filterExpensive')->middleware('admin');
	
	
	Route::any('assignFilter','orderController@assignFilter')->middleware('admin');

	//carriers
	Route::get('/carriers', 'carriersController@carriers')->name('carriers')->middleware('admin');
	Route::post('/addCarrier', 'carriersController@addCarrier')->middleware('admin');
	Route::post('/editCarrier', 'carriersController@editCarrier')->middleware('admin');
	Route::delete('/carrierDelete/{id}','carriersController@delCarrier')->name('carrierDelete')->middleware('admin');

	//Managers
	Route::get('/managers', 'managersController@index')->name('managers')->middleware('admin');
	Route::post('/assignManager', 'managersController@assignOperators')->middleware('admin');	

	//gmail Integration
	Route::get('/gmailAccounts', 'gmailController@accounts')->name('gmailAccounts')->middleware('admin');
	Route::post('/addGmailAccount', 'gmailController@addAccount')->middleware('admin');
	Route::post('/editGmailAccount', 'gmailController@editAccount')->middleware('admin');
	Route::delete('/gmailAccountDelete/{id}','gmailController@delAccount')->name('gmailAccountDelete')->middleware('admin');

	//Products Route
	
	Route::get('/products', 'productsController@index')->name('products')->middleware('admin');
	Route::get('/secondaryproducts', 'productsController@secondaryProducts')->name('secondaryproducts')->middleware('admin');

	Route::post('/upload', 'productsController@uploadSubmit');
	Route::post('/deleteProducts', 'productsController@deleteProducts');
	Route::post('/uploadwm', 'productsController@uploadWmFile');
	Route::post('/manualReprice', 'productsController@manualReprice');



	Route::any('productexport','productsController@export')->name('productexport')->middleware('admin');	
	Route::any('secondaryproductexport','productsController@secondaryExport')->name('secondaryproductexport')->middleware('admin');	

	Route::any('productsfilter','productsController@filter')->middleware('admin');
	Route::any('secondaryfilter','productsController@secondaryFilter')->middleware('admin');
	
	Route::get('deleteProduct/{id}','productsController@deleteProduct')->middleware('admin');
	Route::get('deleteSecondaryProduct/{id}','productsController@deleteSecondaryProduct')->middleware('admin');
	Route::get('/template', 'productsController@getTemplate');
	Route::get('/repTemplate', 'productsController@getRepTemplate');
	Route::get('/addTemplate', 'productsController@getAddTemplate');
	Route::get('/delTemplate', 'productsController@getDelTemplate');
	Route::get('/wmtemplate', 'productsController@getWMTemplate');

	Route::get('repricing','productsController@repricing');
	Route::get('secondaryrepricing','productsController@secondaryRepricing');

	Route::post('getfile','productsController@getFile');
	Route::post('getsecondaryfile','productsController@secondaryGetFile');
	
	Route::post('exportAsins','productsController@exportAsins');
	Route::post('secondaryExportAsins','productsController@secondaryExportAsins');

	Route::get('logs','productsController@getLogs')->name('logs')->middleware('admin');
	Route::get('logsSecondary','productsController@getLogsSecondary')->name('logsSecondary')->middleware('admin');
	Route::post('getLogs','productsController@getLogBatches')->name('getLogBatches')->middleware('admin');
	Route::post('/editAmzProduct', 'productsController@editAmzProduct')->middleware('admin');


	Route::get('/amazonSettings', 'amazonSettingsController@amazonsettings')->name('amazonsettings');
	Route::post('/storeAmazonSettings', 'amazonSettingsController@storeSettings');
	
	//eBay Route
	Route::get('/products/ebay', 'ebayController@index')->name('ebayProducts')->middleware('admin');	
	Route::post('/addEbayProduct', 'ebayController@addProduct');
	Route::post('/updateEbayProduct', 'ebayController@updateProduct');
	Route::get('/getProduct/{id}','ebayController@getProduct');
	Route::delete('/ebayProductDelete/{id}','ebayController@delProduct')->name('ebayProductDelete');
	Route::any('ebayproductsfilter','ebayController@filter')->middleware('admin');
	Route::any('ebayproductexport','ebayController@export')->middleware('admin');
	Route::post('ebayupload','ebayController@uploadSubmit');
	Route::get('/products/ebaytemplate', 'ebayController@getTemplate');
	
	//pricing strategies
	Route::get('/strategies', 'strategiesController@strategies')->name('strategies')->middleware('admin');
	Route::post('/addStrategy', 'strategiesController@addStrategy')->middleware('admin');
	Route::post('/editStrategy', 'strategiesController@editStrategy')->middleware('admin');
	Route::delete('/strategyDelete/{id}','strategiesController@delStrategy')->name('strategyDelete')->middleware('admin');

	//ebay pricing strategies
	Route::get('/ebay/strategies', 'ebayStrategiesController@strategies')->name('ebaystrategies')->middleware('admin');
	Route::post('/ebay/addStrategy', 'ebayStrategiesController@addStrategy')->middleware('admin');
	Route::post('/ebay/editStrategy', 'ebayStrategiesController@editStrategy')->middleware('admin');
	Route::delete('/ebay/strategyDelete/{id}','ebayStrategiesController@delStrategy')->name('ebayStrategyDelete')->middleware('admin');


	//return center
	Route::get('/waitingReturns', 'returnsController@index')->name('returns')->middleware('admin');
	Route::get('/waitingRefunds', 'returnsController@refunds')->name('refunds')->middleware('admin');
	Route::get('/completedReturns', 'returnsController@completed')->name('completed')->middleware('admin');
	Route::post('/addreturn', 'returnsController@addReturn')->middleware('admin');
	Route::post('/editreturn', 'returnsController@editReturn')->middleware('admin');	
	Route::delete('/deleteReturn/{id}','returnsController@deleteReturn')->name('deleteReturn')->middleware('admin');
	Route::delete('/deleteReturn/{route}/{id}','returnsController@deleteReturnRoute')->middleware('admin');
	Route::post('returnsupload','returnsController@uploadSubmit');
	Route::post('uploadLabel','returnsController@uploadLabel');
	Route::get('updateStatus','returnsController@updateStatus');
	Route::get('labelPrint/{id}','returnsController@labelPrint');
	Route::get('labelDelete/{id}','returnsController@labelDelete');
	Route::get('labelDelete/{route}/{id}','returnsController@labelDeleteRoute');		
	Route::any('returnFilter','returnsController@returnFilter')->middleware('admin');
	Route::any('refundFilter','returnsController@refundFilter')->middleware('admin');
	Route::any('completedFilter','returnsController@completedFilter')->middleware('admin');
    
    //accounting

	//bank accounts
	Route::get('/bankaccounts', 'bankAccountsController@index')->name('bankaccounts')->middleware('admin');
	Route::post('/addBank', 'bankAccountsController@addBank')->middleware('admin');
	Route::post('/editBank', 'bankAccountsController@editBank')->middleware('admin');
	Route::delete('/bankDelete/{id}','bankAccountsController@delBank')->name('bankDelete')->middleware('admin');

	//accounting categories
	Route::get('/categories', 'categoriesController@index')->name('categories')->middleware('admin');
	Route::post('/addCategory', 'categoriesController@addCategory')->middleware('admin');
	Route::post('/editCategory', 'categoriesController@editCategory')->middleware('admin');
	Route::delete('/categoryDelete/{id}','categoriesController@delCategory')->name('categoryDelete')->middleware('admin');

	//Transactions Accounting
	Route::get('/transactions','accountingController@transactions')->name('transactions')->middleware('admin');
	Route::get('/processedtransactions','accountingController@processedtransactions')->name('processedtransactions')->middleware('admin');
	Route::post('/transactionsUpload', 'accountingController@uploadSubmit')->name('transactionsUpload')->middleware('admin');
	Route::delete('/transactionDelete/{id}','accountingController@delTransaction')->name('transactionDelete')->middleware('admin');
	Route::get('/getTransaction/{id}','accountingController@getTransaction');
	Route::post('/editTransaction', 'accountingController@editTransaction')->middleware('admin');
	Route::post('assignTransaction','accountingController@assignTransaction')->middleware('admin');
	Route::any('transactionFilter','accountingController@filter')->middleware('admin');
	Route::any('processedtransactionFilter','accountingController@processedfilter')->middleware('admin');
	Route::any('transactionexport','accountingController@export')->middleware('admin');
	Route::any('processedtransactionexport','accountingController@processedexport')->middleware('admin');
	Route::get('/transactionstemplate', 'accountingController@getTemplate');

	//blacklist routes
	Route::get('/blacklist', 'blacklistController@index')->name('blacklist')->middleware('admin');
	Route::post('/addBlacklist', 'blacklistController@addBlacklist')->middleware('admin');
	Route::post('/editBlacklist', 'blacklistController@editBlacklist')->middleware('admin');
	Route::delete('/blacklistDelete/{id}','blacklistController@delBlacklist')->name('blacklistDelete')->middleware('admin');
	Route::post('/blacklistImport', 'blacklistController@import')->middleware('admin');
	Route::any('/blacklistExport','blacklistController@export')->middleware('admin');
	Route::get('/blacklistTemplate', 'blacklistController@getTemplate');

	

	//Walmart Products
	Route::get('/products/walmart', 'walmartController@index')->name('walmartProducts')->middleware('admin');	
	Route::delete('/walmartProductDelete/{id}','walmartController@delProduct')->name('walmartProductDelete');
	Route::any('walmartproductsfilter','walmartController@filter')->middleware('admin');
	Route::any('walmartproductexport','walmartController@export')->middleware('admin');

	
	//Reports routes
	Route::get('/productReport', ['as'=>'product.report',   'uses'=>'ProductReportController@index'])->middleware('admin');
	Route::get('/productReport/orders', ['as'=>'product.report.orders',   'uses'=>'ProductReportController@orders'])->middleware('admin');
	Route::get('/soldReport', ['as'=>'sold.report',   'uses'=>'SoldReportController@index'])->middleware('admin');
	Route::get('/salesReport', ['as'=>'sales.report',   'uses'=>'SalesReportController@index'])->middleware('admin');
	Route::get('/purchaseReport', ['as'=>'purchase.report',   'uses'=>'PurchaseReportController@index'])->middleware('admin');

	//SC Settings Routes
	Route::get('/scaccounts', 'scAccountsController@accounts')->name('scaccounts')->middleware('admin');
	Route::post('/addSCAccount', 'scAccountsController@addAccount')->middleware('admin');
	Route::post('/editSCAccount', 'scAccountsController@editAccount')->middleware('admin');
	Route::delete('/scAccountDelete/{id}','scAccountsController@delAccount')->name('scAccountDelete')->middleware('admin');

	//Informed Settings Routes
	Route::get('/informedSettings', 'informedSettingsController@settings')->name('informed')->middleware('admin');
	Route::post('/addInfCode', 'informedSettingsController@addSetting')->middleware('admin');
	Route::post('/editInfCode', 'informedSettingsController@editSetting')->middleware('admin');
	Route::delete('/infCodeDelete/{id}','informedSettingsController@delSetting')->name('infCodeDelete')->middleware('admin');

	//Multiple Informed Accounts	
	Route::get('/informedAccounts', 'informedAccountsController@accounts')->name('informedaccounts')->middleware('admin');
	Route::post('/addInfAccount', 'informedAccountsController@addAccount')->middleware('admin');
	Route::post('/editInfAcount', 'informedAccountsController@editAccount')->middleware('admin');
	Route::delete('/infAccCodeDelete/{id}','informedAccountsController@delAccount')->name('infAccCodeDelete')->middleware('admin');

	//Keepa Routes
	Route::get('keepa','keepaController@index')->middleware('admin');
	Route::post('getkeepa','keepaController@getResponse')->middleware('admin');

	//Cindy auto fulfillment settings

	Route::get('/orderFulfillmentSetting', 'orderFulfillmentController@index')->name('orderFulfillmentSetting');
	Route::post('/storeSettings', 'orderFulfillmentController@storeSettings')->name('storeSettings');
	Route::any('/orderFulfillmentExport', 'orderFulfillmentController@export')->name('orderFulfillmentExport');
	Route::delete('deleteCancelled/{id}','orderFulfillmentController@deleteCancelled')->name('deleteCancelled')->middleware('admin');	
	Route::delete('deleteConversion/{id}','orderFulfillmentController@deleteConversion')->name('deleteConversion')->middleware('admin');	
	Route::any('/orderCancelledExport', 'orderFulfillmentController@orderCancelledExport')->name('orderCancelledExport');
	Route::post('updateBCE','orderFulfillmentController@updateBCE')->name('updateBCE');

	//cindy orders
	Route::get('autoFulfillProcess','orderFulfillmentController@autoFulfillProcess')->name('autoFulfillProcess')->middleware('admin');	
	
	//Route::get('autofulfillconversions','orderFulfillmentController@autofulfillconversions')->name('cindybce')->middleware('admin');	
	Route::get('autofulfillProcessed','orderFulfillmentController@autofulfillProcessed')->name('cindyprocessed')->middleware('admin');	

	Route::get('autofulfillCancel','orderFulfillmentController@autofulfillCancel')->name('cindycancel')->middleware('admin');		
	Route::get('autoFulfill','orderFulfillmentController@autoFulfill')->name('cindynew')->middleware('admin');	
	Route::any('autofulfillexport','orderFulfillmentController@autofulfillexport')->name('autofulfillexport')->middleware('admin');	
	Route::any('autoFulfillFilter','orderFulfillmentController@autoFulfillFilter')->middleware('admin');
	Route::any('cindysearch','orderFulfillmentController@search')->name('cindysearch');

	//Cindy Returns
	
	Route::get('/autofulfillReturnPending', 'cindyReturnsController@index')->name('cindyreturn')->middleware('admin');
	Route::get('/autofulfillRefundPending', 'cindyReturnsController@refunds')->name('cindyrefund')->middleware('admin');
	Route::get('/autofulfillCompletedReturns', 'cindyReturnsController@completed')->name('cindycompleted')->middleware('admin');
	Route::post('/autofulfillAddreturn', 'cindyReturnsController@addReturn')->middleware('admin');
	Route::post('/autofulfillEditreturn', 'cindyReturnsController@editReturn')->middleware('admin');
	Route::delete('/autofulfillDeleteReturn/{id}','cindyReturnsController@deleteReturn')->name('autofulfillDeleteReturn')->middleware('admin');
	Route::post('autofulfillReturnsupload','cindyReturnsController@uploadSubmit');
	Route::post('autofulfillUploadLabel','cindyReturnsController@uploadLabel');
	Route::get('autofulfillUpdateStatus','cindyReturnsController@updateStatus');
	Route::get('autofulfillLabelPrint/{id}','cindyReturnsController@labelPrint');
	Route::get('autofulfillLabelDelete/{id}','cindyReturnsController@labelDelete');
	Route::any('autofulfillReturnFilter','cindyReturnsController@returnFilter')->middleware('admin');
	Route::any('autofulfillRefundFilter','cindyReturnsController@refundFilter')->middleware('admin');
	Route::any('autofulfillCompletedFilter','cindyReturnsController@completedFilter')->middleware('admin');
	Route::get('autofulfillLabelDelete/{route}/{id}','cindyReturnsController@labelDeleteRoute');
	Route::delete('/autofulfillDeleteReturn/{route}/{id}','cindyReturnsController@deleteReturnRoute')->middleware('admin');

	//vaughn auto fulfillment settings
	Route::get('/vaughnSetting', 'vaughnController@index')->name('vaughnSetting');
	Route::post('/vaughnStoreSettings', 'vaughnController@storeSettings')->name('vaughnStoreSettings');
	Route::any('/vaughnOrderFulfillmentExport', 'vaughnController@export')->name('vaughnOrderFulfillmentExport');
	Route::delete('vaughnDeleteCancelled/{id}','vaughnController@deleteCancelled')->name('vaughnDeleteCancelled')->middleware('admin');	
	Route::delete('vaughnDeleteConversion/{id}','vaughnController@deleteConversion')->name('vaughnDeleteConversion')->middleware('admin');	
	Route::any('/vaughnOrderCancelledExport', 'vaughnController@orderCancelledExport')->name('vaughnOrderCancelledExport');
	Route::post('vaughnUpdateBCE','vaughnController@updateBCE')->name('vaughnUpdateBCE');

	//vaughn orders
	Route::get('vaughnProcess','vaughnController@autoFulfillProcess')->name('vaughnProcess')->middleware('admin');	
	Route::get('vaughnconversions','vaughnController@autofulfillconversions')->name('vaughnbce')->middleware('admin');	
	Route::get('vaughnProcessed','vaughnController@autofulfillProcessed')->name('vaughnprocessed')->middleware('admin');	
	Route::get('vaughnCancel','vaughnController@autofulfillCancel')->name('vaughncancel')->middleware('admin');		
	Route::get('vaughn','vaughnController@autoFulfill')->name('vaughnnew')->middleware('admin');	
	Route::any('vaughnexport','vaughnController@vaughnexport')->name('vaughnexport')->middleware('admin');	
	Route::any('vaughnFilter','vaughnController@autoFulfillFilter')->middleware('admin');
	Route::any('vaughnsearch','vaughnController@search')->name('vaughnsearch');


	//vaughn Returns
	
	Route::get('/vaughnReturnPending', 'vaughnReturnsController@index')->name('vaughnreturn')->middleware('admin');
	Route::get('/vaughnRefundPending', 'vaughnReturnsController@refunds')->name('vaughnrefund')->middleware('admin');
	Route::get('/vaughnCompletedReturns', 'vaughnReturnsController@completed')->name('vaughncompleted')->middleware('admin');
	Route::post('/vaughnAddreturn', 'vaughnReturnsController@addReturn')->middleware('admin');
	Route::post('/vaughnEditreturn', 'vaughnReturnsController@editReturn')->middleware('admin');
	Route::delete('/vaughnDeleteReturn/{id}','vaughnReturnsController@deleteReturn')->name('vaughnDeleteReturn')->middleware('admin');
	Route::post('vaughnReturnsupload','vaughnReturnsController@uploadSubmit');
	Route::post('vaughnUploadLabel','vaughnReturnsController@uploadLabel');
	Route::get('vaughnUpdateStatus','vaughnReturnsController@updateStatus');
	Route::get('vaughnLabelPrint/{id}','vaughnReturnsController@labelPrint');
	Route::get('vaughnLabelDelete/{id}','vaughnReturnsController@labelDelete');
	Route::any('vaughnReturnFilter','vaughnReturnsController@returnFilter')->middleware('admin');
	Route::any('vaughnRefundFilter','vaughnReturnsController@refundFilter')->middleware('admin');
	Route::any('vaughnCompletedFilter','vaughnReturnsController@completedFilter')->middleware('admin');
	Route::get('vaughnLabelDelete/{route}/{id}','vaughnReturnsController@labelDeleteRoute');
	Route::delete('/vaughnDeleteReturn/{route}/{id}','vaughnReturnsController@deleteReturnRoute')->middleware('admin');

	//jonathan auto fulfillment settings
	Route::get('/jonathanSetting', 'jonathanController@index')->name('jonathanSetting');
	Route::post('/jonathanStoreSettings', 'jonathanController@storeSettings')->name('jonathanStoreSettings');
	Route::get('/stateSettings', 'jonathanController@stateSettings')->name('stateSettings');
	Route::post('/storeStateSettings', 'jonathanController@storeStateSettings')->name('storeStateSettings');
	Route::any('/jonathanOrderFulfillmentExport', 'jonathanController@export')->name('jonathanOrderFulfillmentExport');
	Route::delete('jonathanDeleteCancelled/{id}','jonathanController@deleteCancelled')->name('jonathanDeleteCancelled')->middleware('admin');	
	Route::delete('jonathanDeleteConversion/{id}','jonathanController@deleteConversion')->name('jonathanDeleteConversion')->middleware('admin');	
	Route::any('/jonathanOrderCancelledExport', 'jonathanController@orderCancelledExport')->name('jonathanOrderCancelledExport');
	Route::post('jonathanUpdateBCE','jonathanController@updateBCE')->name('jonathanUpdateBCE');

	//jonathan orders
	Route::get('jonathanProcess','jonathanController@autoFulfillProcess')->name('jonathanProcess')->middleware('admin');	
	Route::get('jonathanconversions','jonathanController@autofulfillconversions')->name('jonathanbce')->middleware('admin');	
	Route::get('jonathanProcessed','jonathanController@autofulfillProcessed')->name('jonathanprocessed')->middleware('admin');	
	Route::get('jonathanCancel','jonathanController@autofulfillCancel')->name('jonathancancel')->middleware('admin');		
	Route::get('jonathan','jonathanController@autoFulfill')->name('jonathannew')->middleware('admin');	
	Route::any('jonathanexport','jonathanController@jonathanexport')->name('jonathanexport')->middleware('admin');	
	Route::any('jonathanFilter','jonathanController@autoFulfillFilter')->middleware('admin');
	Route::any('jonathansearch','jonathanController@search')->name('jonathansearch');


	//jonathan Returns
	
	Route::get('/jonathanReturnPending', 'jonathanReturnsController@index')->name('jonathanreturn')->middleware('admin');
	Route::get('/jonathanRefundPending', 'jonathanReturnsController@refunds')->name('jonathanrefund')->middleware('admin');
	Route::get('/jonathanCompletedReturns', 'jonathanReturnsController@completed')->name('jonathancompleted')->middleware('admin');
	Route::post('/jonathanAddreturn', 'jonathanReturnsController@addReturn')->middleware('admin');
	Route::post('/jonathanEditreturn', 'jonathanReturnsController@editReturn')->middleware('admin');
	Route::delete('/jonathanDeleteReturn/{id}','jonathanReturnsController@deleteReturn')->name('jonathanDeleteReturn')->middleware('admin');
	Route::post('jonathanReturnsupload','jonathanReturnsController@uploadSubmit');
	Route::post('jonathanUploadLabel','jonathanReturnsController@uploadLabel');
	Route::get('jonathanUpdateStatus','jonathanReturnsController@updateStatus');
	Route::get('jonathanLabelPrint/{id}','jonathanReturnsController@labelPrint');
	Route::get('jonathanLabelDelete/{id}','jonathanReturnsController@labelDelete');
	Route::any('jonathanReturnFilter','jonathanReturnsController@returnFilter')->middleware('admin');
	Route::any('jonathanRefundFilter','jonathanReturnsController@refundFilter')->middleware('admin');
	Route::any('jonathanCompletedFilter','jonathanReturnsController@completedFilter')->middleware('admin');
	Route::get('jonathanLabelDelete/{route}/{id}','jonathanReturnsController@labelDeleteRoute');
	Route::delete('/jonathanDeleteReturn/{route}/{id}','jonathanReturnsController@deleteReturnRoute')->middleware('admin');

	//jonathan2 auto fulfillment settings
	Route::get('/jonathan2Setting', 'jonathan2Controller@index')->name('jonathan2Setting');
	Route::post('/jonathan2StoreSettings', 'jonathan2Controller@storeSettings')->name('jonathan2StoreSettings');
	Route::any('/jonathan2OrderFulfillmentExport', 'jonathan2Controller@export')->name('jonathan2OrderFulfillmentExport');
	Route::delete('jonathan2DeleteCancelled/{id}','jonathan2Controller@deleteCancelled')->name('jonathan2DeleteCancelled')->middleware('admin');	
	Route::delete('jonathan2DeleteConversion/{id}','jonathan2Controller@deleteConversion')->name('jonathan2DeleteConversion')->middleware('admin');	
	Route::any('/jonathan2OrderCancelledExport', 'jonathan2Controller@orderCancelledExport')->name('jonathan2OrderCancelledExport');
	Route::post('jonathan2UpdateBCE','jonathan2Controller@updateBCE')->name('jonathan2UpdateBCE');

	//jonathan2 orders
	Route::get('jonathan2Process','jonathan2Controller@autoFulfillProcess')->name('jonathan2Process')->middleware('admin');	
	Route::get('jonathan2conversions','jonathan2Controller@autofulfillconversions')->name('jonathan2bce')->middleware('admin');	
	Route::get('jonathan2Processed','jonathan2Controller@autofulfillProcessed')->name('jonathan2processed')->middleware('admin');	
	Route::get('jonathan2Cancel','jonathan2Controller@autofulfillCancel')->name('jonathan2cancel')->middleware('admin');		
	Route::get('jonathan2','jonathan2Controller@autoFulfill')->name('jonathan2new')->middleware('admin');	
	Route::any('jonathan2export','jonathan2Controller@jonathan2export')->name('jonathan2export')->middleware('admin');	
	Route::any('jonathan2Filter','jonathan2Controller@autoFulfillFilter')->middleware('admin');
	Route::any('jonathan2search','jonathan2Controller@search')->name('jonathan2search');


	//jonathan2 Returns
	
	Route::get('/jonathan2ReturnPending', 'jonathan2ReturnsController@index')->name('jonathan2return')->middleware('admin');
	Route::get('/jonathan2RefundPending', 'jonathan2ReturnsController@refunds')->name('jonathan2refund')->middleware('admin');
	Route::get('/jonathan2CompletedReturns', 'jonathan2ReturnsController@completed')->name('jonathan2completed')->middleware('admin');
	Route::post('/jonathan2Addreturn', 'jonathan2ReturnsController@addReturn')->middleware('admin');
	Route::post('/jonathan2Editreturn', 'jonathan2ReturnsController@editReturn')->middleware('admin');
	Route::delete('/jonathan2DeleteReturn/{id}','jonathan2ReturnsController@deleteReturn')->name('jonathan2DeleteReturn')->middleware('admin');
	Route::post('jonathan2Returnsupload','jonathan2ReturnsController@uploadSubmit');
	Route::post('jonathan2UploadLabel','jonathan2ReturnsController@uploadLabel');
	Route::get('jonathan2UpdateStatus','jonathan2ReturnsController@updateStatus');
	Route::get('jonathan2LabelPrint/{id}','jonathan2ReturnsController@labelPrint');
	Route::get('jonathan2LabelDelete/{id}','jonathan2ReturnsController@labelDelete');
	Route::any('jonathan2ReturnFilter','jonathan2ReturnsController@returnFilter')->middleware('admin');
	Route::any('jonathan2RefundFilter','jonathan2ReturnsController@refundFilter')->middleware('admin');
	Route::any('jonathan2CompletedFilter','jonathan2ReturnsController@completedFilter')->middleware('admin');
	Route::get('jonathan2LabelDelete/{route}/{id}','jonathan2ReturnsController@labelDeleteRoute');
	Route::delete('/jonathan2DeleteReturn/{route}/{id}','jonathan2ReturnsController@deleteReturnRoute')->middleware('admin');


	//yaballe auto fulfillment settings
	Route::get('/yaballeSetting', 'yaballeController@index')->name('yaballeSetting');
	Route::post('/yaballeStoreSettings', 'yaballeController@storeSettings')->name('yaballeStoreSettings');
	Route::any('/yaballeOrderFulfillmentExport', 'yaballeController@export')->name('yaballeOrderFulfillmentExport');
	Route::delete('yaballeDeleteCancelled/{id}','yaballeController@deleteCancelled')->name('yaballeDeleteCancelled')->middleware('admin');	
	Route::delete('yaballeDeleteConversion/{id}','yaballeController@deleteConversion')->name('yaballeDeleteConversion')->middleware('admin');	
	Route::any('/yaballeOrderCancelledExport', 'yaballeController@orderCancelledExport')->name('yaballeOrderCancelledExport');
	Route::post('yaballeUpdateBCE','yaballeController@updateBCE')->name('yaballeUpdateBCE');

	//yaballe orders
	Route::post('yaballeProcess','yaballeController@autoFulfillProcess')->name('yaballeProcess')->middleware('admin');	
	Route::get('yaballeconversions','yaballeController@autofulfillconversions')->name('yaballebce')->middleware('admin');	
	Route::get('yaballeProcessed','yaballeController@autofulfillProcessed')->name('yaballeprocessed')->middleware('admin');	
	Route::get('yaballeCancel','yaballeController@autofulfillCancel')->name('yaballecancel')->middleware('admin');		
	Route::get('yaballe','yaballeController@autoFulfill')->name('yaballenew')->middleware('admin');	
	Route::any('yaballeexport','yaballeController@yaballeexport')->name('yaballeexport')->middleware('admin');	
	Route::any('yaballeOrderExport','yaballeController@yaballeOrderExport')->name('yaballeOrderExport')->middleware('admin');
	Route::any('yaballeFilter','yaballeController@autoFulfillFilter')->middleware('admin');
	Route::any('yaballesearch','yaballeController@search')->name('yaballesearch');


	//yaballe Returns

	Route::get('/yaballeReturnPending', 'yaballeReturnsController@index')->name('yaballereturn')->middleware('admin');
	Route::get('/yaballeRefundPending', 'yaballeReturnsController@refunds')->name('yaballerefund')->middleware('admin');
	Route::get('/yaballeCompletedReturns', 'yaballeReturnsController@completed')->name('yaballecompleted')->middleware('admin');
	Route::post('/yaballeAddreturn', 'yaballeReturnsController@addReturn')->middleware('admin');
	Route::post('/yaballeEditreturn', 'yaballeReturnsController@editReturn')->middleware('admin');
	Route::delete('/yaballeDeleteReturn/{id}','yaballeReturnsController@deleteReturn')->name('yaballeDeleteReturn')->middleware('admin');
	Route::post('yaballeReturnsupload','yaballeReturnsController@uploadSubmit');
	Route::post('yaballeUploadLabel','yaballeReturnsController@uploadLabel');
	Route::get('yaballeUpdateStatus','yaballeReturnsController@updateStatus');
	Route::get('yaballeLabelPrint/{id}','yaballeReturnsController@labelPrint');
	Route::get('yaballeLabelDelete/{id}','yaballeReturnsController@labelDelete');
	Route::any('yaballeReturnFilter','yaballeReturnsController@returnFilter')->middleware('admin');
	Route::any('yaballeRefundFilter','yaballeReturnsController@refundFilter')->middleware('admin');
	Route::any('yaballeCompletedFilter','yaballeReturnsController@completedFilter')->middleware('admin');
	Route::get('yaballeLabelDelete/{route}/{id}','yaballeReturnsController@labelDeleteRoute');
	Route::delete('/yaballeDeleteReturn/{route}/{id}','yaballeReturnsController@deleteReturnRoute')->middleware('admin');
	
	//order flags
	Route::get('/flags', 'flagsController@flags')->name('flags')->middleware('admin');
	Route::post('/addFlag', 'flagsController@addFlag')->middleware('admin');
	Route::post('/editFlag', 'flagsController@editFlag')->middleware('admin');
	Route::post('/editExpensive', 'flagsController@editExpensive')->middleware('admin');
	Route::delete('/FlagDelete/{id}','flagsController@delFlag')->name('flagDelete')->middleware('admin');

	//blacklist reasons
	Route::get('/reasons', 'reasonsController@reasons')->name('reasons')->middleware('admin');
	Route::post('/addReason', 'reasonsController@addReason')->middleware('admin');
	Route::post('/editReason', 'reasonsController@editReason')->middleware('admin');
	Route::delete('/ReasonDelete/{id}','reasonsController@delReason')->name('reasonDelete')->middleware('admin');

	//Autofulfillment Manager
	Route::get('/afManager', 'AutoFulfillmentController@index')->name('afManager');
	Route::post('/afStoreSettings', 'AutoFulfillmentController@storeSettings')->name('afStoreSettings');
});

