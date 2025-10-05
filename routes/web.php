<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/ping', fn () => 'pong '.now());

Route::get('/', fn () => Inertia::render('Home'))->name('home');

Route::get('/style-guide', fn () => Inertia::render('StyleGuide'))->name('style-guide');

require __DIR__.'/auth.php';
