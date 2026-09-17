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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RecipesController extends Controller
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
        ->orderBy('recipes.id')
        ->distinct()
        ->get();

        return view('recipes.index', compact('recipes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $occasions = Occasion::all();
        $levels = Level::all();
        $units = MeasurementUnit::all();

        return view(
            'recipes.create',
            compact('categories', 'occasions', 'levels', 'units')
        );
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipe_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'recipe' => ['required', 'string', 'max:255'],
            'categories_id' => ['required', 'integer', 'exists:categories,id'],
            'occasions_id' => ['required', 'integer', 'exists:occasions,id'],
            'levels_id' => ['required', 'integer', 'exists:levels,id'],
            'preparation_time' => ['required', 'integer', 'min:0'],
            'cooking_time' => ['required', 'integer', 'min:0'],
            'portions' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string'],
            'preparation_instructions' => ['required', 'string'],

            'ingredients' => ['required', 'array', 'min:1'],
            'ingredients.*' => ['required', 'string', 'max:255'],

            'amounts' => ['required', 'array'],
            'amounts.*' => ['nullable', 'string', 'max:255'],

            'units' => ['required', 'array'],
            'units.*' => [
                'required',
                'integer',
                'exists:measurement_units,id',
            ],
        ]);

        $imagePath = $request
            ->file('recipe_image')
            ->store('public/imgs');

        $filename = basename($imagePath);

        try {
            DB::transaction(function () use ($validated, $filename) {
                $recipe = Recipe::create([
                    'name' => $validated['recipe'],
                    'image' => $filename,
                    'preparation_time' => $validated['preparation_time'],
                    'cooking_time' => $validated['cooking_time'],
                    'total_time' =>
                        $validated['preparation_time']
                        + $validated['cooking_time'],
                    'description' => $validated['description'],
                    'preparation_instructions' =>
                        $validated['preparation_instructions'],
                    'portions' => $validated['portions'],
                    'levels_id' => $validated['levels_id'],
                ]);

                RecipeHasCategory::create([
                    'recipes_id' => $recipe->id,
                    'categories_id' => $validated['categories_id'],
                ]);

                RecipeHasOccasion::create([
                    'recipes_id' => $recipe->id,
                    'occasions_id' => $validated['occasions_id'],
                ]);

                RecipeHasLike::create([
                    'recipes_id' => $recipe->id,
                    'likes' => 0,
                ]);

                foreach ($validated['ingredients'] as $index => $ingredientName) {
                    $ingredient = Ingredient::create([
                        'name' => $ingredientName,
                        'description' => $ingredientName,
                    ]);

                    RecipeHasIngredient::create([
                        'recipes_id' => $recipe->id,
                        'ingredients_id' => $ingredient->id,
                        'measurement_units_id' => $validated['units'][$index],
                        'amount' => $validated['amounts'][$index] ?? '',
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            Storage::delete($imagePath);

            throw $exception;
        }

        return redirect()
            ->route('recipes.index')
            ->with('success', 'Recipe registered successfully.');
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
        ->where('recipes.id', $id)
        ->firstOrFail();

        $ingredients = Recipe::select(
            'recipes.id',
            'ingredients.name',
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

        return view('recipes.show', compact('recipe', 'ingredients'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $recipe = Recipe::findOrFail($id);

        $categories = Category::all();
        $occasions = Occasion::all();
        $levels = Level::all();
        $units = MeasurementUnit::all();

        $categoryId = RecipeHasCategory::where('recipes_id', $id)
            ->value('categories_id');

        $occasionId = RecipeHasOccasion::where('recipes_id', $id)
            ->value('occasions_id');

        $ingredients = RecipeHasIngredient::select(
            'ingredients.id',
            'ingredients.name',
            'recipe_has_ingredients.amount',
            'recipe_has_ingredients.measurement_units_id'
        )
            ->join(
                'ingredients',
                'ingredients.id',
                '=',
                'recipe_has_ingredients.ingredients_id'
            )
            ->where('recipe_has_ingredients.recipes_id', $id)
            ->where('recipe_has_ingredients.status', 1)
            ->where('ingredients.status', 1)
            ->get();

        return view('recipes.edit', compact(
            'recipe',
            'categories',
            'occasions',
            'levels',
            'units',
            'categoryId',
            'occasionId',
            'ingredients'
        ));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $recipe = Recipe::findOrFail($id);

        $validated = $request->validate([
            'recipe_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'recipe' => ['required', 'string', 'max:255'],
            'categories_id' => ['required', 'integer', 'exists:categories,id'],
            'occasions_id' => ['required', 'integer', 'exists:occasions,id'],
            'levels_id' => ['required', 'integer', 'exists:levels,id'],

            'preparation_time' => [
                'required', 'integer', 'min:0', 'max:1000000',
            ],
            'cooking_time' => [
                'required', 'integer', 'min:0', 'max:1000000',
            ],
            'portions' => [
                'required', 'integer', 'min:1', 'max:1000000',
            ],

            'description' => ['required', 'string', 'max:10000'],
            'preparation_instructions' => [
                'required', 'string', 'max:10000',
            ],

            'ingredients' => ['required', 'array', 'min:1', 'max:100'],
            'ingredients.*' => ['required', 'string', 'max:255'],

            'amounts' => ['required', 'array', 'max:100'],
            'amounts.*' => ['nullable', 'string', 'max:255'],

            'units' => ['required', 'array', 'max:100'],
            'units.*' => [
                'required',
                'integer',
                'exists:measurement_units,id',
            ],
        ]);

        // Cada ingrediente debe tener una cantidad y unidad asociadas.
        $ingredientKeys = array_keys($validated['ingredients']);

        if (
            $ingredientKeys !== array_keys($validated['amounts'])
            || $ingredientKeys !== array_keys($validated['units'])
        ) {
            throw ValidationException::withMessages([
                'ingredients' => 'Each ingredient must have an amount field and a unit.',
            ]);
        }

        $newImagePath = null;

        if ($request->hasFile('recipe_image')) {
            $newImagePath = $request->file('recipe_image')
                ->store('imgs', 'public');

            if ($newImagePath === false) {
                throw ValidationException::withMessages([
                    'recipe_image' => 'The image could not be saved. Please try again.',
                ]);
            }
        }

        try {
            DB::transaction(function () use ($recipe, $validated, $newImagePath) {
                // Evita que dos ediciones modifiquen simultáneamente esta receta.
                $recipe = Recipe::whereKey($recipe->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $recipe->update([
                    'name' => $validated['recipe'],
                    'image' => $newImagePath !== null
                        ? basename($newImagePath)
                        : $recipe->image,
                    'preparation_time' => $validated['preparation_time'],
                    'cooking_time' => $validated['cooking_time'],
                    'total_time' => $validated['preparation_time']
                        + $validated['cooking_time'],
                    'description' => $validated['description'],
                    'preparation_instructions' =>
                        $validated['preparation_instructions'],
                    'portions' => $validated['portions'],
                    'levels_id' => $validated['levels_id'],
                ]);

                RecipeHasCategory::updateOrCreate(
                    ['recipes_id' => $recipe->id],
                    ['categories_id' => $validated['categories_id']]
                );

                RecipeHasOccasion::updateOrCreate(
                    ['recipes_id' => $recipe->id],
                    ['occasions_id' => $validated['occasions_id']]
                );

                $oldIngredientIds = RecipeHasIngredient::where(
                    'recipes_id',
                    $recipe->id
                )->pluck('ingredients_id');

                // Elimina solo las relaciones de esta receta.
                RecipeHasIngredient::where('recipes_id', $recipe->id)
                    ->delete();

                // Elimina los ingredientes anteriores que quedaron sin relaciones.
                Ingredient::whereIn('id', $oldIngredientIds)
                    ->whereNotIn('id', function ($query) {
                        $query->select('ingredients_id')
                            ->from('recipe_has_ingredients');
                    })
                    ->delete();

                foreach ($validated['ingredients'] as $index => $name) {
                    $ingredient = Ingredient::create([
                        'name' => $name,
                        'description' => $name,
                    ]);

                    RecipeHasIngredient::create([
                        'recipes_id' => $recipe->id,
                        'ingredients_id' => $ingredient->id,
                        'measurement_units_id' => $validated['units'][$index],
                        'amount' => $validated['amounts'][$index] ?? '',
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            // Si falla la base, elimina únicamente la imagen recién subida.
            if ($newImagePath !== null) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('recipes.index')
            ->with('success', 'Recipe updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function unpublishRecipe(string $id)
    {
        $recipe = Recipe::findOrFail($id);

        $recipe->status = 0;
        $recipe->save();

        return redirect()
            ->route('recipes.index')
            ->with('success', 'Recipe unpublished successfully.');
    }

    public function publishRecipe(string $id)
    {
        $recipe = Recipe::findOrFail($id);

        $recipe->status = 1;
        $recipe->save();

        return redirect()
            ->route('recipes.unpublish')
            ->with('success', 'Recipe published successfully.');
    }

    public function getUnpublishedRecipes()
    {
        //
        $recipes = Recipe::select(
            'recipes.id',
            'recipes.name',
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
        ->orderBy('recipes.id')
        ->where('recipes.status', 0)
        ->get();

        return view('recipes.unpublish', compact('recipes'));

    }
}
