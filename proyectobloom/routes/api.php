<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecipesCollection;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//end points for recipes
Route::get('/recipes/all', [RecipesCollection::class, 'index']);
Route::get('/recipes/recipe/{id}', [RecipesCollection::class, 'show'])->whereNumber('id');
Route::get('/recipes/categories', [RecipesCollection::class, 'categories']);
Route::get('/recipes/levels', [RecipesCollection::class, 'levels']);
Route::get('/recipes/occasions', [RecipesCollection::class, 'occasions']);
Route::get('/recipes/top10', [RecipesCollection::class, 'top10']);
Route::get('/recipes/filterby/{filter}/{value}', [RecipesCollection::class, 'getRecipesBy'])->whereNumber('value');
Route::get('/recipes/searchbyname/{value}', [RecipesCollection::class, 'searchByName']);


//end points for users
Route::post('/users/register', [AuthController::class, 'register']);
Route::post('/users/login', [AuthController::class, 'login']);
Route::post('/users/recoverpassword', [AuthController::class, 'recoverPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/logout', [AuthController::class, 'logout']);

    Route::get('/users/likedrecipes', [AuthController::class, 'getLikedRecipes']);

    Route::post('/users/likes/{recipe}', [AuthController::class, 'likes'])
        ->whereNumber('recipe');

    Route::delete('/users/dislikes/{recipe}', [AuthController::class, 'dislikes'])
        ->whereNumber('recipe');

    Route::post('/users/saverecipe/{recipe}', [AuthController::class, 'saveRecipe'])
        ->whereNumber('recipe');

    Route::get('/users/savedrecipes', [AuthController::class, 'getSavedRecipes']);

    Route::delete('/users/removesavedrecipe/{recipe}', [AuthController::class, 'removeSavedRecipe'])
        ->whereNumber('recipe');
});
