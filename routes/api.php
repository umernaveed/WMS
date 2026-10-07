<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Auth::routes();

Route::post('GetUsers','API\V1\ApiController@GetUsers');
Route::get('getPackages','API\V1\ApiController@getPackages');
Route::post('saveUser','API\V1\ApiController@saveUser');
Route::post('updateUser','API\V1\ApiController@updateUser');

Route::get('updatePackageStatus','API\V1\ApiController@updatePackageStatusNew');

Route::post('updateManifestNumber','API\V1\ApiController@updateManifestNumber');

Route::get('updateManifestStatus','API\V1\ApiController@updateManifestStatus');

Route::post('GetPackageCategories','API\V1\ApiController@GetPackageCategories');
Route::post('GetVendors','API\V1\ApiController@GetVendorsAdd');
Route::post('GetPackagesTypes','API\V1\ApiController@GetPackagesTypesAdd');

Route::prefix('saas')->group(function () {
    Route::post('couriers', 'API\V1\SaasSyncController@courier');
    Route::post('customers', 'API\V1\SaasSyncController@customer');
    Route::post('packages', 'API\V1\SaasSyncController@package');
    Route::post('manifests', 'API\V1\SaasSyncController@manifest');
});



Route::middleware('auth:api')->group(function() {
   
});
