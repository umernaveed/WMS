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
Route::get('/check_phpinfo', function() {
    return phpinfo();
});
 Route::get('/config-cache', function() {
    $exitCode = Artisan::call('config:cache');
 });
    Route::get('/clear-cache', function() {
    $exitCode = Artisan::call('cache:clear');
    });
    
    Route::get('/test-user-api','Admin\LoginControllor@testPackage');
    Route::get('/import-packages','Admin\PackegControllor@importPackages');
    Route::get('/update_package_type','Admin\LoginControllor@packageType');
Route::GET('/test/invoices', 'Admin\InvoiceControllor@testInvoice');
//Upload Product Image Routes
Route::post('product/files/upload','Vendor\KartsControllor@uploadFile');
Route::post('product/delete_img/{name}','Vendor\KartsControllor@deleteFile');

//Admin Auth Routes
Route::match(['post', 'get'],'/','Admin\LoginControllor@showLoginForm');
Route::match(['post', 'get'],'/login','Admin\LoginControllor@showLoginForm');
Route::match(['post', 'get'],'/admin','Admin\LoginControllor@showLoginForm');
Route::get('/admin/logout','Admin\LoginControllor@logout');
Route::match(['post', 'get'],'/admin/forget/password','Admin\LoginControllor@forgetPassword');
Route::match(['post', 'get'],'/user/reset_password/{id}', 'Admin\LoginControllor@resetUserPassword');
Route::match(['post', 'get'],'/admin/reset_password/{id}', 'Admin\LoginControllor@resetPassword');

Route::match(['post', 'get'],'/register','Admin\LoginControllor@adminRegister');

Route::get('/admin/upload','Admin\InvoiceControllor@uploadUserCSV');
Route::get('/admin/upload/weight','Admin\InvoiceControllor@ratesUploadCSV');
Route::match(['post', 'get'], '/admin/calculater/home','Admin\RateControllor@calculaterHome');

Route::get('/admin/delivery/packages', 'Admin\PickupRequestControllor@indexDelivery');
Route::get('/admin/invoice/unpaid','Admin\DashboardControllor@unpaidInvoice');

Route::get('/get-members/{type}/{keyword}','Admin\UserControllor@getMembers');

Route::group(['middleware' => 'admin'], function () {
    Route::get('/admin/dashboard','Admin\DashboardControllor@dashboard');
    Route::get('/admin/global-search','Admin\DashboardControllor@globalSearch');
    Route::match(['get', 'post'], '/admin/portal-settings','Admin\PortalFeatureControllor@settings');
    Route::get('/admin/portal/reports','Admin\PortalFeatureControllor@reports');
    Route::get('/admin/portal/{module}','Admin\PortalFeatureControllor@index');
    Route::post('/admin/portal/{module}','Admin\PortalFeatureControllor@store');
    Route::get('/admin/portal/{module}/{id}/edit','Admin\PortalFeatureControllor@edit');
    Route::post('/admin/portal/{module}/{id}','Admin\PortalFeatureControllor@update');
    Route::post('/admin/portal/{module}/{id}/delete','Admin\PortalFeatureControllor@destroy');
    

    Route::get('/admin/update/user/package','Admin\DashboardControllor@updateUserName');
    Route::get('/admin/update/status/package','Admin\DashboardControllor@updatePackageStatusName');
    Route::get('/admin/update/invoice/username','Admin\DashboardControllor@updateInvoiceName');
    
    Route::match(['post', 'get'],'/admin/activies', 'Admin\ActivityControllor@view')->name('activies');
    Route::GET('activies-list', 'Admin\ActivityControllor@listView')->name('activies-list');
    Route::get('admin/save/activity/logs/{type}/{title}','Admin\ActivityControllor@saveLog');

    Route::match(['post', 'get'],'admin/profile','Admin\DashboardControllor@profile');
    Route::match(['post', 'get'],'admin/settings','Admin\DashboardControllor@settings');
    Route::match(['post', 'get'],'admin/settings','Admin\DashboardControllor@settings');
    Route::GET('ready-list', 'Admin\DashboardControllor@listViewReady')->name('package-ready-list');
    Route::GET('recent-list', 'Admin\DashboardControllor@listViewRecent')->name('recent-list');
    
    Route::get('admin/processing-analytics', 'Admin\DashboardControllor@getProcessingAnalytics')->name('admin.processing.analytics');
    
    
    //Admin users Routes
    Route::GET('/admin/users/list', 'Admin\AdminControllor@view')->name('admin-list');
    Route::GET('admin-list', 'Admin\AdminControllor@listView')->name('admin-list');
    Route::match(['post', 'get'], '/admin/user/create','Admin\AdminControllor@create');
    Route::match(['post', 'get'],'/admin/edit/{id}','Admin\AdminControllor@edit');
    Route::get('/admin/delete/{id}','Admin\AdminControllor@delete');

    //Roles Routes
    Route::GET('/admin/roles', 'Admin\RoleControllor@view')->name('roles');
    Route::GET('role-list', 'Admin\RoleControllor@listView')->name('role-list');
    Route::post('/save-role','Admin\RoleControllor@save');
    Route::post('/delete-role','Admin\AdminControllor@delete');
    
    //Roles & Permission Routs
    Route::get('/admin/roles/new','Admin\RoleNewControllor@index');
    Route::get('/admin/permission/assign','Admin\RoleNewControllor@permission');
    Route::match(['post', 'get'], '/admin/roles/add','Admin\RoleNewControllor@create');
    Route::get('/admin/roles/delete/{role_id}','Admin\RoleNewControllor@delete');
    Route::match(['post', 'get'],'/admin/roles/edit/{role_id}','Admin\RoleNewControllor@edit');
    Route::get('/admin/assign/role/{module_id}/{role_id}','Admin\RoleNewControllor@assignRole');
    Route::get('/admin/unassign/role/{module_id}/{role_id}','Admin\RoleNewControllor@unassignRole');
    Route::get('/admin/role/permission/{role_id}','Admin\RoleNewControllor@ajaxPermission');
    //Users Routes
    //Route::get('/admin/users','Admin\UserControllor@index');
    //Route::GET('/admin/users', 'Admin\UserControllor@view')->name('users');
    Route::GET('/admin/users', 'Admin\UserControllor@view')->name('user-list');
    Route::GET('users-list', 'Admin\UserControllor@listView')->name('users-list');
    Route::get('/admin/users/{status}/{id}','Admin\UserControllor@updateStatus');
    Route::match(['post', 'get'],'/admin/user/add','Admin\UserControllor@create');
    Route::match(['post', 'get'],'/admin/user/edit/{id}','Admin\UserControllor@edit');
    Route::get('/admin/user/delete/{id}','Admin\UserControllor@delete');
    Route::get('/admin/user/profile/{id}','Admin\UserControllor@userProfile');
    Route::GET('/user-ready-list/{mailbox}', 'Admin\UserControllor@listViewReady')->name('user-ready-list');
    //purchase request Routes
    Route::GET('/admin/purchase/request', 'Admin\PurchaseControllor@view')->name('purchases');
    Route::GET('purchase-list', 'Admin\PurchaseControllor@listView')->name('purchase-list');
    //Outlets Routes
    Route::GET('/admin/outlets', 'Admin\OutletControllor@view')->name('outlets');
    Route::GET('outlets-list', 'Admin\OutletControllor@listView')->name('outlets-list');
    Route::match(['post', 'get'], '/admin/outlet/add','Admin\OutletControllor@create');
    Route::post('/delete-outlet','Admin\OutletControllor@delete');
    Route::match(['post', 'get'],'/admin/outlet/edit/{id}','Admin\OutletControllor@edit');
    Route::GET('admin/outlet/packages/{id}', 'Admin\OutletControllor@viewPackages')->name('outlets-packages');
    Route::GET('outlets-packages-list/{id}', 'Admin\OutletControllor@listViewPackages')->name('outlets-packages-list');
    //Packages Routes
    Route::GET('/admin/posts', 'Admin\PackegControllor@viewPost')->name('packeges-all');
    Route::post('allposts', 'Admin\PackegControllor@allPosts')->name('allposts');
    Route::GET('/posts.show.{id}', 'Admin\PackegControllor@edit')->name('edit');
    Route::GET('/admin/courier/users/{id}/{trecking_number}', 'Admin\UserControllor@getCourierUsers');
    Route::GET('/admin/mailbox/lookup/{mailbox}', 'Admin\UserControllor@lookupMailbox');

    Route::GET('/admin/get/package/all', 'Admin\PackegControllor@viewAll')->name('packeges-all');
    Route::GET('packeges-all-list', 'Admin\PackegControllor@listViewAll')->name('packeges-all-list');

    Route::GET('/admin/packeges/{type}/{id}', 'Admin\PackegControllor@view')->name('packeges');
    Route::GET('packeges-list/{type}/{id}', 'Admin\PackegControllor@listView')->name('packeges-list');
    Route::GET('/admin/status/packeges/{type}', 'Admin\PackegControllor@viewStatus')->name('packeges-status');
    Route::GET('packeges-status-list/{type}', 'Admin\PackegControllor@listViewStatus')->name('packeges-status-list');


    Route::GET('/admin/category/packages/{type}', 'Admin\PackegControllor@viewCategory')->name('packeges-category');
    Route::GET('packeges-category-list/{type}', 'Admin\PackegControllor@listViewCategory')->name('packeges-category-list');

     Route::GET('admin/package/request', 'Admin\PackegControllor@requestView')->name('admin-request');
    Route::GET('admin-request-list', 'Admin\PackegControllor@requestListView')->name('admin-request-list');
    Route::GET('/admin/request/packages/{status}/{id}', 'Admin\PackegControllor@updatePackageRequest');



    Route::get('/admin/last/packeges','Admin\PackegControllor@latestPackeges');
    Route::get('/admin/packege/update','Admin\PackegControllor@update');
    Route::match(['post', 'get'],'/admin/create/manifest','Admin\PackegControllor@createMani');
    Route::match(['post', 'get'],'/admin/packege/update/status','Admin\PackegControllor@updateStatus');
    Route::match(['post', 'get'], '/admin/packege/add','Admin\PackegControllor@create');
    Route::match(['post', 'get'],'/admin/package/add/{id}','Admin\PackegControllor@addPackage');
    Route::match(['post', 'get'],'/admin/package/edit/{id}','Admin\PackegControllor@edit');
    Route::post('/delete-package','Admin\PackegControllor@delete');
    Route::post('/resend-package','Admin\PackegControllor@resendPackage');
    Route::get('/admin/packege/import/sample','Admin\PackegControllor@downloadImportSample');
    Route::match(['post', 'get'], '/admin/packege/import','Admin\PackegControllor@import');
    Route::get('/admin/package/check/tracking/number/{id}/{c_id}','Admin\PackegControllor@checkTrackingNum');
    Route::get('/admin/download/label/{id}','Admin\PackegControllor@downloadLabel');
    Route::get('/admin/downloadNew/label/{id}','Admin\PackegControllor@downloadLabelNew');
    Route::post('/admin/packeges/mark-in-transit', 'Admin\PackegControllor@markInTransit');
// Test route for mass resend
Route::get('admin/package/test-resend/{courier_id}', function($courier_id) {
    return response()->json([
        'success' => true,
        'message' => 'Test successful for courier: ' . $courier_id,
        'data' => ['courier_id' => $courier_id]
    ]);
});
// Mass Resend Routes
Route::get('admin/package/mass-resend', 'Admin\PackegControllor@massResend')->name('admin.package.mass-resend');
Route::post('admin/package/mass-resend/{courier_id}', 'Admin\PackegControllor@processMassResend');
Route::post('admin/package/mass-resend/all', 'Admin\PackegControllor@processMassResendAll');
Route::get('admin/package/count/{courier_id}', 'Admin\PackegControllor@getCourierPackageCount');


    Route::get('/admin/user/packeges/{id}','Admin\PackegControllor@packeges');
    Route::get('/admin/packege/weight/{id}/{courier}','Admin\PackegControllor@weight');
    Route::get('/admin/packeges/weight/edit/{id}/{inv_id}','Admin\PackegControllor@weightEdit');
    Route::get('/admin/temporary/packeges','Admin\PackegControllor@temporaryPackage');
    //Package status
    Route::get('/admin/package/status','Admin\StatusControllor@index');
    Route::match(['post', 'get'], '/admin/status/add','Admin\StatusControllor@create');
    Route::get('/admin/status/delete/{id}','Admin\StatusControllor@delete');
    Route::match(['post', 'get'],'/admin/status/edit/{id}','Admin\StatusControllor@edit');
    //Rate Routes
    Route::get('/admin/rates','Admin\RateControllor@index');
    Route::match(['post', 'get'], '/admin/rate/add','Admin\RateControllor@create');
    //Route::get('/admin/status/delete/{id}','Admin\StatusControllor@delete');
    Route::match(['post', 'get'],'/admin/rate/edit/{id}','Admin\RateControllor@edit');
    //Invoice Route
    Route::GET('/admin/invoices', 'Admin\InvoiceControllor@view')->name('invoices');
    Route::GET('invoices-list', 'Admin\InvoiceControllor@listView')->name('invoices-list');
    Route::GET('/admin/invoices/email/{status}', 'Admin\InvoiceControllor@viewEmail')->name('invoices-email');
    Route::GET('invoices-email-list/{status}', 'Admin\InvoiceControllor@listViewEmail')->name('invoices-email-list');

    Route::match(['post', 'get'],'/admin/invoices/mass/payments/{status}', 'Admin\InvoiceControllor@viewMassPayments')->name('invoices-mass-payments');
    Route::GET('invoices-mass-payments-list/{status}', 'Admin\InvoiceControllor@listViewMassPayments')->name('invoices-mass-payments-list');

    Route::match(['post', 'get'], '/admin/invoice/add','Admin\InvoiceControllor@create');
    Route::post('/delete-invoice','Admin\InvoiceControllor@delete');
    
    Route::GET('/admin/invoice/history/{id}', 'Admin\InvoiceControllor@viewHistory')->name('invoices-history');
    Route::GET('invoices-list-history/{id}', 'Admin\InvoiceControllor@listViewHistory')->name('invoices-list-history');
    
    Route::post('/update-partial-pay','Admin\InvoiceControllor@partialPay');
    Route::match(['post', 'get'],'/admin/invoice/edit/{id}','Admin\InvoiceControllor@edit');
    Route::get('/admin/invoice/print/{id}','Admin\InvoiceControllor@printInvoice');
    Route::get('/user/invoices','Admin\InvoiceControllor@userInvoices');
    Route::match(['post', 'get'],'/admin/invoice/find/package','Admin\InvoiceControllor@findPackeg');
    Route::match(['post', 'get'],'/admin/create/invoice','Admin\InvoiceControllor@createInvoice');
    Route::post('/admin/invoice/submit','Admin\InvoiceControllor@submitInvoice');
    Route::get('/admin/invoice/{id}','Admin\InvoiceControllor@invoice');
    Route::get('/admin/invoice/print/{id}','Admin\InvoiceControllor@invoicePrint');
    Route::post('/admin/invoice/send','Admin\InvoiceControllor@sendInvoice');
    Route::get('/admin/resend/email/{id}','Admin\InvoiceControllor@resendInvoice');
    Route::get('/admin/invoice/update/{status}/{id}','Admin\InvoiceControllor@updateInvoiceStatus');
    Route::match(['post', 'get'],'/admin/invoices/export','Admin\InvoiceControllor@exportInvoice');
    Route::match(['post', 'get'],'/admin/invoices/export_download','Admin\InvoiceControllor@exportDownloadInvoice');
    Route::get('/admin/invpackege/delete/{id}','Admin\PackegControllor@deletePackage');
    Route::get('/admin/invpackege/delete/edit/{id}','Admin\PackegControllor@deletePackageEdit');
    //Old Invoice Route
    Route::GET('/admin/old/invoices', 'Admin\InvoiceOldControllor@view')->name('invoices-old');
    Route::GET('invoices-list-old', 'Admin\InvoiceOldControllor@listView')->name('invoices-list-old');
    Route::GET('/admin/old/invoices/email/{status}', 'Admin\InvoiceOldControllor@viewEmail')->name('invoices-email-old');
    Route::GET('invoices-email-list-old/{status}', 'Admin\InvoiceOldControllor@listViewEmail')->name('invoices-email-list-old');
    Route::get('/admin/old/resend/email/{id}','Admin\InvoiceOldControllor@resendInvoice');
    Route::get('/admin/old/invoice/update/{status}/{id}','Admin\InvoiceOldControllor@updateInvoiceStatus');
    Route::get('/admin/old/invoice/{id}','Admin\InvoiceOldControllor@invoice');
    Route::post('/delete-invoice-old','Admin\InvoiceOldControllor@delete');
    //End

    Route::match(['post', 'get'],'admin/change/password','Admin\DashboardControllor@changePassword');

    //Weight Routes
    Route::GET('/admin/weight', 'Admin\WeightControllor@view')->name('weight');
    Route::GET('weight-list', 'Admin\WeightControllor@listView')->name('weight-list');
    Route::post('/save-weight','Admin\WeightControllor@save');
    //Send Email Routes
    Route::match(['post', 'get'],'/send/email','Admin\DashboardControllor@sendEmail');
    //Menifest Routes
    //Route::get('/admin/package/manifest/{id}','Admin\PackegControllor@manifest');
    Route::get('/admin/package/export/excel/{id}','Admin\PackegControllor@exportExcel');
    Route::match(['post', 'get'],'/admin/manifest/export/{id}','Admin\PackegControllor@exportManifest');

    Route::GET('/admin/list/manifest/{id}', 'Admin\PackegControllor@viewMenifest')->name('manifest');
    Route::GET('packeges-menifest-list/{id}', 'Admin\PackegControllor@listViewMenifest')->name('packeges-menifest-list');
    Route::match(['post', 'get'],'/admin/create/manifest','Admin\PackegControllor@createMani');
    Route::match(['post', 'get'],'/admin/manifest/create','Admin\PackegControllor@createManifest');
    Route::match(['post', 'get'],'/admin/manifest/edit/{id}','Admin\PackegControllor@editManifest');
    Route::get('/admin/manifest/packege/delete/{id}','Admin\PackegControllor@deleteManifest');
    
    Route::GET('/admin/package/manifest/{id}', 'Admin\PackegControllor@viewMenifestList')->name('manifest-list');
    Route::GET('view-menifest-list/{id}', 'Admin\PackegControllor@listViewPackageMenifest')->name('view-menifest-list');
    Route::post('/delete-manifest','Admin\PackegControllor@deleteManifestNew');
    
    //Location Route
    Route::GET('/admin/pickup/location', 'Admin\LocationControllor@view')->name('location');
    Route::GET('location-list', 'Admin\LocationControllor@listView')->name('location-list');
    Route::match(['post', 'get'], '/save-location','Admin\LocationControllor@save');
    
    //Areas Routes
    Route::GET('/admin/areas', 'Admin\AreaControllor@view')->name('address-areas');
    Route::GET('areas-list', 'Admin\AreaControllor@listView')->name('areas-list');
    Route::match(['post', 'get'],'/save-area','Admin\AreaControllor@save');
    Route::POST('/delete-area','Admin\AreaControllor@delete');
    Route::GET('/admin/area/{status}/{id}','Admin\AreaControllor@updateStatus');

    Route::match(['post', 'get'],'/admin/schedule/days','Admin\AreaControllor@scheduleDays');

    //Delivery Routes
    Route::GET('/admin/delivery/schedule/{type}', 'Admin\PickupRequestControllor@view')->name('requests');
    Route::GET('requests-list/{type}', 'Admin\PickupRequestControllor@listView')->name('requests-list');
    Route::GET('admin/request/{status}/{id}', 'Admin\PickupRequestControllor@updateStatus');

    // courier routes
    Route::get('/admin/couriers','Admin\CourierControllor@index');
    Route::match(['post', 'get'],'admin/courier/create','Admin\CourierControllor@add');
    Route::match(['post', 'get'],'/admin/couriers/edit/{id}','Admin\CourierControllor@edit');
    Route::post('/admin/courier/reset-password/{id}','Admin\CourierControllor@resetPassword');
    Route::get('/admin/courier/delete/{id}','Admin\CourierControllor@delete');
    Route::post('/import-users','Admin\CourierControllor@importUser');
    Route::post('/import-cms-users','Admin\CourierControllor@importAllCmsUsers');



    // courier routes
    Route::get('/admin/carriers','Admin\CarrierControllor@index');
    Route::match(['post', 'get'],'admin/carrier/add','Admin\CarrierControllor@add');
    Route::match(['post', 'get'],'/admin/carrier/edit/{id}','Admin\CarrierControllor@edit');
    Route::get('/admin/carrier/delete/{id}','Admin\CarrierControllor@delete');

     // Package Type routes
    Route::get('/admin/package/type','Admin\PackageTypeController@index');
    Route::match(['post', 'get'],'admin/package/type/create','Admin\PackageTypeController@add');
    Route::match(['post', 'get'],'/admin/package/type/edit/{id}','Admin\PackageTypeController@edit');
    Route::get('/admin/package/type/delete/{id}','Admin\PackageTypeController@delete');

    Route::GET('/admin/packages/delivery/history', 'Admin\PickupRequestControllor@viewHistory')->name('requestsHistory');
    Route::GET('requests-history-list-admin', 'Admin\PickupRequestControllor@listHistoryView')->name('requests-history-list-admin');
    
    //Weight Routes
    Route::GET('/admin/weight', 'Admin\WeightControllor@view')->name('weight');
    Route::GET('weight-list', 'Admin\WeightControllor@listView')->name('weight-list');
    Route::post('/save-weight','Admin\WeightControllor@save');
    
});

Route::group(['middleware' => 'courier'], function () {
    
    Route::get('/courier/api/documentations','Courier\ApidocumentControllor@index');
    Route::post('/courier/test/api','Courier\ApidocumentControllor@testApi');
    Route::post('courier/set-base-url', 'Courier\ApidocumentControllor@updateBaseUrl');

    Route::match(['post', 'get'],'courier/user/add','Courier\UserControllor@add');
    Route::GET('/courier/users', 'Courier\UserControllor@view')->name('coruser-list');
    Route::GET('coruser-list', 'Courier\UserControllor@listView')->name('coruser-list');
    Route::get('/courier/dashboard','Courier\DashboardControllor@dashboard');
    Route::match(['post', 'get'],'courier/profile','Courier\DashboardControllor@profile');
    Route::match(['post', 'get'],'courier/change/password','Courier\DashboardControllor@changePassword');
    Route::match(['post', 'get'],'outlet/profile','Courier\DashboardControllor@profile');
    Route::GET('outlet-ready-list', 'Courier\DashboardControllor@listViewReady')->name('outlet-ready-list');
    Route::GET('outlet-recent-list', 'Courier\DashboardControllor@listViewRecent')->name('outlet-recent-list');
    Route::match(['post', 'get'],'outlet/change/password','Courier\DashboardControllor@changePassword');
    Route::post('/courier/user/delete','Courier\UserControllor@delete');
    Route::match(['post', 'get'], '/courier/packege/add','Courier\PackegControllor@add');
    Route::GET('/courier/packeges/track', 'Courier\PackegControllor@view')->name('courier-packeges');
    Route::GET('courier-packeges-list', 'Courier\PackegControllor@listView')->name('courier-packeges-list');
    Route::GET('courier-packeges-export/{scope}', 'Courier\PackegControllor@exportPackages')->name('courier-packeges-export');
    Route::GET('courier/package/miscellaneous', 'Courier\PackegControllor@miscellaneousView')->name('courier-miscellaneous');
    Route::GET('courier-miscellaneous-list', 'Courier\PackegControllor@miscellaneousListView')->name('courier-miscellaneous-list');
    Route::GET('/courier/packeges/transit', 'Courier\PackegControllor@viewtransit')->name('courier-transit');
    Route::GET('courier-transit-list', 'Courier\PackegControllor@listtransitListView')->name('courier-transit-list');
    Route::match(['post', 'get'],'/courier/package/edit/{id}','Courier\PackegControllor@updatePackage');
   // Route::match(['post', 'get'],'/courier/package/add/{id}','Courier\PackegControllor@edit');
    Route::match(['post', 'get'],'/courier/package/request/{id}','Courier\PackegControllor@updatePackage');
    
    Route::match(['post', 'get'],'/courier/user/edit/{id}','Courier\UserControllor@edit');
    

    Route::GET('/courier/invoices', 'Courier\InvoiceControllor@view')->name('courier-invoices');
    Route::GET('courier-invoices-list', 'Courier\InvoiceControllor@listView')->name('courier-invoices-list');
    Route::get('/courier/invoice/{id}','Courier\InvoiceControllor@invoice');

    //Menifest Routes
    Route::get('/courier/package/manifest','Courier\PackegControllor@manifest');
    Route::get('/courier/package/export/excel/{id}','Courier\PackegControllor@exportExcel');
    Route::match(['post', 'get'],'/courier/manifest/export/{id}','Courier\PackegControllor@exportManifest');

    Route::GET('/courier/list/manifest', 'Courier\PackegControllor@viewMenifest')->name('manifest');
    Route::GET('courier-menifest-list', 'Courier\PackegControllor@listViewMenifest')->name('courier-menifest-list');
    Route::match(['post', 'get'],'/courier/create/manifest','Courier\PackegControllor@createMani');
    Route::match(['post', 'get'],'/courier/manifest/create','Courier\PackegControllor@createManifest');
    Route::match(['post', 'get'],'/courier/manifest/edit/{id}','Courier\PackegControllor@editManifest');
    Route::get('/courier/manifest/packege/delete/{id}','Courier\PackegControllor@deleteManifest');

    Route::GET('/outlet/users', 'Courier\UserControllor@view')->name('outlet-users');
    Route::GET('outlet-users-list', 'Courier\UserControllor@listView')->name('outlet-users-list');

    Route::GET('/outlet/packeges', 'Courier\PackegControllor@view')->name('outlet-packeges');
    Route::GET('outlet-packeges-list', 'Courier\PackegControllor@listView')->name('outlet-packeges-list');
    Route::get('/outlet/package/status/update/{status}/{id}','Courier\PackegControllor@updateStatus');

    Route::GET('/outlet/invoices', 'Courier\InvoiceControllor@view')->name('outlet-invoices');
    Route::GET('outlet-invoices-list', 'Courier\InvoiceControllor@listView')->name('outlet-invoices-list');
    Route::get('/outlet/invoice/{id}','Courier\InvoiceControllor@invoice');
    Route::match(['post', 'get'], '/outlet/calculater','Courier\RateControllor@calculater');
});

Route::group(['middleware' => 'user'], function () {
    Route::get('/user/dashboard','Users\DashboardControllor@dashboard');
    Route::match(['post', 'get'],'user/profile','Users\DashboardControllor@profile');
    Route::GET('ready-user-list', 'Users\DashboardControllor@listViewReady')->name('ready-user-list');
    Route::GET('recent-user-list', 'Users\DashboardControllor@listViewRecent')->name('recent-user-list');
    Route::match(['post', 'get'],'user/change/password','Users\DashboardControllor@changePassword');

    Route::match(['post', 'get'], '/user/packege/add','Users\PackegControllor@add');
    Route::GET('/user/packeges/track', 'Users\PackegControllor@view')->name('user-packeges');
    Route::GET('user-packeges-list', 'Users\PackegControllor@listView')->name('user-packeges-list');

    Route::match(['post', 'get'],'/user/delivery/packages', 'Users\PickupRequestControllor@index');

    //purchase request Routes
    Route::GET('/user/purchase/request', 'Users\PurchaseControllor@view')->name('user-purchases');
    Route::GET('user-purchase-list', 'Users\PurchaseControllor@listView')->name('user-purchase-list');
    Route::match(['post', 'get'], '/user/purchase/add','Users\PurchaseControllor@create');
    Route::match(['post', 'get'],'/user/purchase/edit/{id}','Users\PurchaseControllor@edit');

    Route::GET('/user/invoices', 'Users\InvoiceControllor@view')->name('user-invoices');
    Route::GET('user-invoices-list', 'Users\InvoiceControllor@listView')->name('user-invoices-list');
    Route::get('/user/invoice/{id}','Users\InvoiceControllor@invoice');

    Route::GET('/user/packages/delivery/history', 'Users\PickupRequestControllor@viewHistory')->name('requestsHistory');
    Route::GET('requests-history-list', 'Users\PickupRequestControllor@listHistoryView')->name('requests-history-list');
});
