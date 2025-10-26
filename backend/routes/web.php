<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['message' => 'Blockchain API is running'];
});
