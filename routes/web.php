<?php

use App\Http\Controllers\BukuController;          
use App\Http\Controllers\Soap\LibrarySoapController; 
use Illuminate\Support\Facades\Route;

Route::get('/soap/sipus.wsdl', [LibrarySoapController::class, 'wsdl']);
Route::post('/soap/sipus', [LibrarySoapController::class, 'handle']);