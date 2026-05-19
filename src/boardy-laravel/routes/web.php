<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\GitHubController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/auth/github', [GitHubController::class, 'redirect'])->name('github.login');
Route::get('/auth/github/callback', [GitHubController::class, 'callback']);

Route::resource('posts', PostController::class);

Route::post('comments', [CommentController::class, 'store'])
    ->middleware('auth')
    ->name('comments.store');

require __DIR__.'/auth.php';
