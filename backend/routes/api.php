<?php

use App\Http\Controllers\TranslationController;
use Illuminate\Support\Facades\Route;

Route::get('/languages', [TranslationController::class, 'languages']);

Route::post('/rooms/{roomId}/pipeline', [TranslationController::class, 'pipeline']);
