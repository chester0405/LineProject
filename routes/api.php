<?php

use App\Http\Controllers\RichMenuController;
use App\Http\Controllers\RichMenuGroupController;
use App\Http\Controllers\UploadController;
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

Route::apiResource('menus', RichMenuController::class)->parameters([
    'menus' => 'richMenu',
]);

Route::apiResource('groups', RichMenuGroupController::class)->parameters([
    'groups' => 'richMenuGroup',
]);

Route::post('upload/image', [UploadController::class, 'upload']);
