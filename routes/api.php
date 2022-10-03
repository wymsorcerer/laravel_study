<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlayersController;
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

// Player API
Route::get('/players', [PlayersController::class, 'index']);
Route::get('/players/{id}', [PlayersController::class, 'show']);
Route::post('/players', [PlayersController::class, 'store']);
Route::put('/players/{id}', [PlayersController::class, 'update']);
Route::delete('/players/{id}', [PlayersController::class, 'destroy']);

// Player Item API
Route::get('/players/itmes/{id}', [PlayersController::class, 'itemList']);
Route::post('/players/itmes/add', [PlayersController::class, 'addItem']);
Route::post('/players/itmes/use', [PlayersController::class, 'useItem']);
Route::post('/players/itmes/purchase', [PlayersController::class, 'purchaseItem']);