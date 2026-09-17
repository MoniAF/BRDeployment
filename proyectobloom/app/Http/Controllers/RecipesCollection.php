<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Category;
use App\Models\Occasion;
use App\Models\Level;
use App\Models\MeasurementUnit;
use App\Models\RecipeHasCategory;
use App\Models\RecipeHasOccasion;
use App\Models\RecipeHasLike;
use App\Models\RecipeHasIngredient;
use App\Models\Ingredient;

class RecipesCollection extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $recipes = Recipe::select(
            'recipes.id',
            'recipes.name',
            'recipes.image',
            'recipes.description',
            'categories.category',
            'occasions.occasion',
            'levels.level',
            'recipe_has_likes.likes'
        )
        ->join('recipe_has_categories', 'recipes.id', '=', 'recipe_has_categories.recipes_id')
        ->join('categories', 'categories.id', '=', 'recipe_has_categories.categories_id')
        ->join('recipe_has_occasions', 'recipes.id', '=', 'recipe_has_occasions.recipes_id')
        ->join('occasions', 'occasions.id', '=', 'recipe_has_occasions.occasions_id')
        ->join('levels', 'recipes.levels_id', '=', 'levels.id')
        ->join('recipe_has_likes', 'recipes.id', '=', 'recipe_has_likes.recipes_id')
        ->where('recipes.status', 1)
        ->orderBy('recipes.id', 'ASC')
        ->distinct()
        ->get();

        return $recipes;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $recipe = Recipe::select(
            'recipes.id',
            'recipes.name',
            'recipes.image',
            'recipes.description',
            'recipes.preparation_time',
            'recipes.cooking_time',
            'recipes.total_time',
            'recipes.preparation_instructions',
            'recipes.portions',
            'categories.id as category_id',
            'categories.category',
            'occasions.occasion',
            'levels.level',
            'recipe_has_likes.likes',
            'recipe_has_categories.categories_id'
        )
        ->join('recipe_has_categories', 'recipes.id', '=', 'recipe_has_categories.recipes_id')
        ->join('categories', 'categories.id', '=', 'recipe_has_categories.categories_id')
        ->join('recipe_has_occasions', 'recipes.id', '=', 'recipe_has_occasions.recipes_id')
        ->join('occasions', 'occasions.id', '=', 'recipe_has_occasions.occasions_id')
        ->join('levels', 'recipes.levels_id', '=', 'levels.id')
        ->join('recipe_has_likes', 'recipes.id', '=', 'recipe_has_likes.recipes_id')
        ->where('recipes.id', $id)
        ->where('recipes.status', 1)
        ->get();

        if ($recipe->isEmpty()) {
            return response()->json([
                'message' => 'Recipe not found',
            ], 404);
        }
        
        //get recipe ingredients list
        $ingredients = Recipe::select(
            'recipes.id',
            'ingredients.description',
            'measurement_units.measurement_unit',
            'recipe_has_ingredients.amount'
        )
        ->join('recipe_has_ingredients', 'recipes.id', '=', 'recipe_has_ingredients.recipes_id')
        ->join('ingredients', 'ingredients.id', '=', 'recipe_has_ingredients.ingredients_id')
        ->join('measurement_units', 'measurement_units.id', '=', 'recipe_has_ingredients.measurement_units_id')
        ->where([
            'recipes.id' => $id,
            'ingredients.status' => 1,
            'recipe_has_ingredients.status' => 1
        ])
        ->get();

        //get related recipes from same category
        $related = Recipe::select(
            'recipes.id',
            'recipes.name',
            'recipes.image',
            'recipes.description',
            'recipes.preparation_time',
            'recipes.cooking_time',
            'recipes.total_time',
            'recipes.preparation_instructions',
            'recipes.portions',
            'categories.category',
            'occasions.occasion',
            'levels.level',
            'recipe_has_likes.likes'
        )
        ->join('recipe_has_categories', 'recipes.id', '=', 'recipe_has_categories.recipes_id')
        ->join('categories', 'categories.id', '=', 'recipe_has_categories.categories_id')
        ->join('recipe_has_occasions', 'recipes.id', '=', 'recipe_has_occasions.recipes_id')
        ->join('occasions', 'occasions.id', '=', 'recipe_has_occasions.occasions_id')
        ->join('levels', 'recipes.levels_id', '=', 'levels.id')
        ->join('recipe_has_likes', 'recipes.id', '=', 'recipe_has_likes.recipes_id')
        ->where(
            'recipe_has_categories.categories_id',
            $recipe[0]->categories_id
        )
        ->where('recipes.status', 1)
        ->where('recipes.id', '!=', $id)
        ->inRandomOrder()
        ->take(4)
        ->get();

        return [$recipe, $ingredients, $related];
    }

    public function categories()
    {
        $categories = Category::select('id', 'category')->get();
        return $categories;
    }

    /**
     * get all available levels
     */
    public function levels()
    {
        $levels = Level::select('id', 'level')->get();
        return $levels;
    }

    /**
     * get all available occasions
     */
    public function occasions()
    {
        $occasions = Occasion::select('id', 'occasion')->get();
        return $occasions;
    }
/**
     * get 10 most voted recipes
     */
    public function top10()
    {
        $recipes = Recipe::select(
            'recipes.id',
            'recipes.name',
            'recipes.image',
            'recipes.description',
            'categories.category',
            'occasions.occasion',
            'levels.level',
            'recipe_has_likes.likes'
        )
        ->join('recipe_has_categories', 'recipes.id', '=', 'recipe_has_categories.recipes_id')
        ->join('categories', 'categories.id', '=', 'recipe_has_categories.categories_id')
        ->join('recipe_has_occasions', 'recipes.id', '=', 'recipe_has_occasions.recipes_id')
        ->join('occasions', 'occasions.id', '=', 'recipe_has_occasions.occasions_id')
        ->join('levels', 'recipes.levels_id', '=', 'levels.id')
        ->join('recipe_has_likes', 'recipes.id', '=', 'recipe_has_likes.recipes_id')
        ->where('recipes.status', 1)
        ->orderBy('recipe_has_likes.likes', 'DESC')
        ->distinct()
        ->take(10)
        ->get();

        return $recipes;
    }

    /**
     * get recipes by filter & value:
     *  $filter = category, level, occasion
     *  $value = filter's int value
     * 
     */
    public function getRecipesBy($filter, $value)
    {
        $allowedFilters = [
            'category' => 'categories.id',
            'level' => 'levels.id',
            'occasion' => 'occasions.id',
        ];

        if (!array_key_exists($filter, $allowedFilters)) {
            return response()->json([
                'message' => 'Invalid filter. Use category, level or occasion.',
            ], 422);
        }

        $tableField = $allowedFilters[$filter];

        $recipes = Recipe::select(
            'recipes.id',
            'recipes.name',
            'recipes.image',
            'recipes.description',
            'categories.category',
            'occasions.occasion',
            'levels.level',
            'recipe_has_likes.likes'
        )
        ->join('recipe_has_categories', 'recipes.id', '=', 'recipe_has_categories.recipes_id')
        ->join('categories', 'categories.id', '=', 'recipe_has_categories.categories_id')
        ->join('recipe_has_occasions', 'recipes.id', '=', 'recipe_has_occasions.recipes_id')
        ->join('occasions', 'occasions.id', '=', 'recipe_has_occasions.occasions_id')
        ->join('levels', 'recipes.levels_id', '=', 'levels.id')
        ->join('recipe_has_likes', 'recipes.id', '=', 'recipe_has_likes.recipes_id')
        ->where($tableField, $value)
        ->where('recipes.status', 1)
        ->orderBy('recipes.id', 'ASC')
        ->distinct()
        ->get();

        return $recipes;
    }

    /**
     * search recipes by recipe name
     * 
     */
    public function searchByName($value)
    {
        //
        $recipes = Recipe::select(
            'recipes.id',
            'recipes.name',
            'recipes.image',
            'recipes.description',
            'categories.category',
            'occasions.occasion',
            'levels.level',
            'recipe_has_likes.likes'
        )
        ->join('recipe_has_categories', 'recipes.id', '=', 'recipe_has_categories.recipes_id')
        ->join('categories', 'categories.id', '=', 'recipe_has_categories.categories_id')
        ->join('recipe_has_occasions', 'recipes.id', '=', 'recipe_has_occasions.recipes_id')
        ->join('occasions', 'occasions.id', '=', 'recipe_has_occasions.occasions_id')
        ->join('levels', 'recipes.levels_id', '=', 'levels.id')
        ->join('recipe_has_likes', 'recipes.id', '=', 'recipe_has_likes.recipes_id')
        ->where('recipes.name', 'LIKE', '%'.$value.'%')
        ->where('recipes.status', 1)
        ->orderBy('recipes.id', 'ASC')
        ->distinct()
        ->get();

        return $recipes;
    }
}
