<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AzamPayController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\TermsAndConditionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Landing Page API Routes (Public)
Route::get('/blocks/{block}/rooms', [LandingPageController::class, 'getRoomsByBlock']);
Route::get('/rooms/{room}/beds', [LandingPageController::class, 'getBedsByRoom']);
Route::get('/terms/active', [TermsAndConditionController::class, 'getActive']);

// AzamPay Gateway API Routes (Public - called by AzamPay)
Route::post('/merchant/name-lookup', [AzamPayController::class, 'nameLookup']);
Route::post('/merchant/payment', [AzamPayController::class, 'payment']);
Route::post('/merchant/status-check', [AzamPayController::class, 'statusCheck']);

