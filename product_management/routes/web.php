<?php

use Illuminate\Support\Facades\Route;

// Backend web page for Product CRUD (uses the REST API in routes/api.php)
Route::view('/', 'products');
