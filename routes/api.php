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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::prefix('customer')->namespace('\App\Http\Controllers\Customer')->group(
    function () {
        Route::any('index', 'IndexController@index');
        Route::any('change', 'IndexController@change');
        Route::any('house', 'IndexController@getChooseHouse');
        Route::any('getAllZjj', 'IndexController@getAllZjj');
    }
);
