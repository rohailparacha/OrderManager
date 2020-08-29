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
	Route::get('newOrdersFlagged','orderController@newOrdersFlagged')->name('newOrdersFlagged')->middleware('admin');
	Route::get('processedOrders','orderController@processedOrders')->name('processedOrders')->middleware('admin');
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

	Route::post('getManualBce','orderController@getManualBce')->middleware('admin');
	Route::get('cancelOrder/{id}','orderController@cancelOrder')->name('cancelOrder');	
	Route::get('sync','orderController@syncOrders')->name('sync')->middleware('admin');	
	Route::any('search','orderController@search')->name('search');
	Route::get('orderDetails/{id}','orderController@details')->name('orderDetails');
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
	Route::get('report','reportsController@index')->name('report')->middleware('admin');
	Route::any('filter','reportsController@filter')->name('filter')->middleware('admin');
	Route::any('export','reportsController@export')->name('export')->middleware('admin');	
	Route::get('getAmzDetails','orderController@getAmazonDetails')->middleware('admin');
	Route::get('orderFlag/{id}/{flag}','orderController@orderFlag')->middleware('admin');
	Route::get('orderFlag/{route}/{id}/{flag}','orderController@orderFlagRoute')->middleware('admin');
	Route::any('orderFilter','orderController@filter')->middleware('admin');
	Route::any('orderFilterFlagged','orderController@filterFlagged')->middleware('admin');
	
	
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
	Route::post('/manualReprice', 'productsController@manualReprice');

	Route::any('productexport','productsController@export')->name('productexport')->middleware('admin');	
	Route::any('secondaryproductexport','productsController@secondaryExport')->name('secondaryproductexport')->middleware('admin');	

	Route::any('productsfilter','productsController@filter')->middleware('admin');
	Route::any('secondaryfilter','productsController@secondaryFilter')->middleware('admin');
	
	Route::get('deleteProduct/{id}','productsController@deleteProduct')->middleware('admin');
	Route::get('deleteSecondaryProduct/{id}','productsController@deleteSecondaryProduct')->middleware('admin');
	Route::get('/template', 'productsController@getTemplate');

	Route::get('repricing','productsController@repricing');
	Route::get('secondaryrepricing','productsController@secondaryRepricing');

	Route::get('getfile','productsController@getFile');
	Route::get('getsecondaryfile','productsController@secondaryGetFile');
	
	Route::post('exportAsins','productsController@exportAsins');
	Route::post('secondaryExportAsins','productsController@secondaryExportAsins');

	Route::get('logs','productsController@getLogs')->name('logs')->middleware('admin');
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

	//samuel auto fulfillment settings
	Route::get('/samuelSetting', 'samuelController@index')->name('samuelSetting');
	Route::post('/samuelStoreSettings', 'samuelController@storeSettings')->name('samuelStoreSettings');
	Route::any('/samuelOrderFulfillmentExport', 'samuelController@export')->name('samuelOrderFulfillmentExport');
	Route::delete('samuelDeleteCancelled/{id}','samuelController@deleteCancelled')->name('samuelDeleteCancelled')->middleware('admin');	
	Route::delete('samuelDeleteConversion/{id}','samuelController@deleteConversion')->name('samuelDeleteConversion')->middleware('admin');	
	Route::any('/samuelOrderCancelledExport', 'samuelController@orderCancelledExport')->name('samuelOrderCancelledExport');
	Route::post('samuelUpdateBCE','samuelController@updateBCE')->name('samuelUpdateBCE');

	//samuel orders
	Route::get('samuelProcess','samuelController@autoFulfillProcess')->name('samuelProcess')->middleware('admin');	
	Route::get('samuelconversions','samuelController@autofulfillconversions')->name('samuelbce')->middleware('admin');	
	Route::get('samuelProcessed','samuelController@autofulfillProcessed')->name('samuelprocessed')->middleware('admin');	
	Route::get('samuelCancel','samuelController@autofulfillCancel')->name('samuelcancel')->middleware('admin');		
	Route::get('samuel','samuelController@autoFulfill')->name('samuelnew')->middleware('admin');	
	Route::any('samuelexport','samuelController@samuelexport')->name('samuelexport')->middleware('admin');	
	Route::any('samuelFilter','samuelController@autoFulfillFilter')->middleware('admin');
	Route::any('samuelsearch','samuelController@search')->name('samuelsearch');


	//samuel Returns
	
	Route::get('/samuelReturnPending', 'samuelReturnsController@index')->name('samuelreturn')->middleware('admin');
	Route::get('/samuelRefundPending', 'samuelReturnsController@refunds')->name('samuelrefund')->middleware('admin');
	Route::get('/samuelCompletedReturns', 'samuelReturnsController@completed')->name('samuelcompleted')->middleware('admin');
	Route::post('/samuelAddreturn', 'samuelReturnsController@addReturn')->middleware('admin');
	Route::post('/samuelEditreturn', 'samuelReturnsController@editReturn')->middleware('admin');
	Route::delete('/samuelDeleteReturn/{id}','samuelReturnsController@deleteReturn')->name('samuelDeleteReturn')->middleware('admin');
	Route::post('samuelReturnsupload','samuelReturnsController@uploadSubmit');
	Route::post('samuelUploadLabel','samuelReturnsController@uploadLabel');
	Route::get('samuelUpdateStatus','samuelReturnsController@updateStatus');
	Route::get('samuelLabelPrint/{id}','samuelReturnsController@labelPrint');
	Route::get('samuelLabelDelete/{id}','samuelReturnsController@labelDelete');
	Route::any('samuelReturnFilter','samuelReturnsController@returnFilter')->middleware('admin');
	Route::any('samuelRefundFilter','samuelReturnsController@refundFilter')->middleware('admin');
	Route::any('samuelCompletedFilter','samuelReturnsController@completedFilter')->middleware('admin');
	Route::get('samuelLabelDelete/{route}/{id}','samuelReturnsController@labelDeleteRoute');
	Route::delete('/samuelDeleteReturn/{route}/{id}','samuelReturnsController@deleteReturnRoute')->middleware('admin');

	//jonathan auto fulfillment settings
	Route::get('/jonathanSetting', 'jonathanController@index')->name('jonathanSetting');
	Route::post('/jonathanStoreSettings', 'jonathanController@storeSettings')->name('jonathanStoreSettings');
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

	//order flags
	Route::get('/flags', 'flagsController@flags')->name('flags')->middleware('admin');
	Route::post('/addFlag', 'flagsController@addFlag')->middleware('admin');
	Route::post('/editFlag', 'flagsController@editFlag')->middleware('admin');
	Route::delete('/FlagDelete/{id}','flagsController@delFlag')->name('flagDelete')->middleware('admin');

	//blacklist reasons
	Route::get('/reasons', 'reasonsController@reasons')->name('reasons')->middleware('admin');
	Route::post('/addReason', 'reasonsController@addReason')->middleware('admin');
	Route::post('/editReason', 'reasonsController@editReason')->middleware('admin');
	Route::delete('/ReasonDelete/{id}','reasonsController@delReason')->name('reasonDelete')->middleware('admin');
});

