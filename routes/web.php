<?php

use App\Http\Controllers\Football\FootballController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/', [FootballController::class, 'liveMatchesPage']); // page
Route::get('/football/live-api', [FootballController::class, 'getLiveMatches']); // ajax
Route::get('/football/fixtures-api', [FootballController::class, 'fixturesApi']);
