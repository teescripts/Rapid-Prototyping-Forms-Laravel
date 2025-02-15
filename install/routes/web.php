<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormController;
use App\Http\Controllers\ListsController;
use App\Http\Controllers\StaticController;

Route::get('/dash', function() {
    return view('porto.porto', ['title'=>"Layout"]);#->name('index.menu')
});

Route::get('/rptforms', [FormController::class, 'index'])->name('form');
Route::get('/Lists/{type}/{name}', [ListsController::class, 'all'])->name('lists.all');
