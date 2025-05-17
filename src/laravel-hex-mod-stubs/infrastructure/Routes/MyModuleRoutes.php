<?php

use Illuminate\Support\Facades\Route;

Route::prefix('{{kebabName}}')->group(function () {
    Route::post('/', Create{{StudlyName}}Controller::class);
});
