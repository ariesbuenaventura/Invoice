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
    return Redirect::to('/login');
});

Auth::routes();

Route::get('/invoices', [App\Http\Controllers\InvoiceController::class, 'index']);
Route::get('/invoices/invoice/{id}/{action}', [App\Http\Controllers\InvoiceController::class, 'invoice']);
Route::get('/invoices/search/{id?}', [App\Http\Controllers\InvoiceController::class, 'search']);

Route::post('/invoices/invoice/save', [App\Http\Controllers\InvoiceController::class, 'save']);
Route::post('/invoices/invoice/delete', [App\Http\Controllers\InvoiceController::class, 'delete']);


