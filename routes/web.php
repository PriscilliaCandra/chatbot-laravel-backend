<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Add login route to handle authentication redirects
Route::get('/login', function () {
    return response()->json(['message' => 'Please login to access this resource'], 401)
        ->header('Content-Type', 'application/json');
})->name('login');
