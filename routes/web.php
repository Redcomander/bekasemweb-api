<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeployController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/deploy-webhook', [DeployController::class, 'webhook']);
