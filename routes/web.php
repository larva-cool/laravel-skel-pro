<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\MainController::class, 'index']);
Route::get('redirect', [\App\Http\Controllers\MainController::class, 'redirect']);
Route::get('headers', [\App\Http\Controllers\MainController::class, 'headers']);

