<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\RecipesController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])
    ->name('login');

Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->name('admin.login.submit');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])
        ->name('admin.logout');

    Route::resource('recipes', RecipesController::class)
    ->except(['destroy']);

    Route::get('/unpublish/recipes', [
        RecipesController::class,
        'getUnpublishedRecipes',
    ])->name('recipes.unpublish');

    Route::patch('/recipes/{id}/publish', [
        RecipesController::class,
        'publishRecipe',
    ])->whereNumber('id')->name('recipes.publish');

    Route::patch('/recipes/{id}/unpublish', [
        RecipesController::class,
        'unpublishRecipe',
    ])->whereNumber('id')->name('recipes.unpublish.submit');
});