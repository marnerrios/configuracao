<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WhatsappController;
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

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('/login',       [AuthController::class, 'login']);
    Route::post('/refresh',     [AuthController::class, 'refresh']);
});
Route::group([
    'prefix' => 'z-api'
], function () {
    Route::post('/mensagemBMG/YKYHr50U5x0XJbHT',       [WhatsappController::class, 'recebeMensagemBmg']);
    Route::post('/mensagemPAN/ACsgjMbqjpdGSE6e',       [WhatsappController::class, 'recebeMensagemPan']);
    Route::get('{cpf}',       [WhatsappController::class, 'calcSalarioBase']);
});

