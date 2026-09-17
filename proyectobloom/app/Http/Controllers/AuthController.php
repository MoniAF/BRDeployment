<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Recipe;
use App\Models\RecipeHasLike;
use App\Models\UsersVoteRecipe;
use App\Models\UsersSaveRecipe;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'country' => $validated['country'],
            'profiles_id' => 2,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function getLikedRecipes(Request $request)
    {
        $recipeIds = UsersVoteRecipe::where(
            'users_id',
            $request->user()->id
        )
            ->whereIn(
                'recipes_id',
                Recipe::where('status', 1)->select('id')
            )
            ->distinct()
            ->pluck('recipes_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        return response()->json([
            'recipe_ids' => $recipeIds,
        ]);
    }

    public function likes(Request $request, $recipe)
    {
        $userId = $request->user()->id;

        return DB::transaction(function () use ($userId, $recipe) {
            $recipeRecord = Recipe::where('id', $recipe)
                ->where('status', 1)
                ->lockForUpdate()
                ->firstOrFail();

            $counter = RecipeHasLike::firstOrCreate(
                ['recipes_id' => $recipeRecord->id],
                [
                    'likes' => UsersVoteRecipe::where(
                        'recipes_id',
                        $recipeRecord->id
                    )->count(),
                ]
            );

            $vote = UsersVoteRecipe::firstOrCreate([
                'users_id' => $userId,
                'recipes_id' => $recipeRecord->id,
            ]);

            if ($vote->wasRecentlyCreated) {
                $counter->increment('likes');
            }

            return response()->json([
                'message' => $vote->wasRecentlyCreated
                    ? 'Recipe liked successfully.'
                    : 'You already liked this recipe.',
                'likes' => (int) $counter->likes,
                'liked' => true,
            ]);
        });
    }

    public function dislikes(Request $request, $recipe)
    {
        $userId = $request->user()->id;

        return DB::transaction(function () use ($userId, $recipe) {
            $recipeRecord = Recipe::where('id', $recipe)
                ->lockForUpdate()
                ->firstOrFail();

            $counter = RecipeHasLike::firstOrCreate(
                ['recipes_id' => $recipeRecord->id],
                [
                    'likes' => UsersVoteRecipe::where(
                        'recipes_id',
                        $recipeRecord->id
                    )->count(),
                ]
            );

            $deleted = UsersVoteRecipe::where('users_id', $userId)
                ->where('recipes_id', $recipeRecord->id)
                ->delete();

            if ($deleted > 0 && $counter->likes > 0) {
                $counter->decrement('likes');
            }

            return response()->json([
                'message' => $deleted > 0
                    ? 'Like removed successfully.'
                    : 'You had not liked this recipe.',
                'likes' => (int) $counter->likes,
                'liked' => false,
            ]);
        });
    }

    public function saveRecipe(Request $request, $recipe)
    {
        $userId = $request->user()->id;

        return DB::transaction(function () use ($userId, $recipe) {
            $recipeRecord = Recipe::where('id', $recipe)
                ->where('status', 1)
                ->lockForUpdate()
                ->firstOrFail();

            $savedRecipe = UsersSaveRecipe::firstOrCreate([
                'users_id' => $userId,
                'recipes_id' => $recipeRecord->id,
            ]);

            return response()->json([
                'message' => $savedRecipe->wasRecentlyCreated
                    ? 'Recipe saved successfully.'
                    : 'This recipe was already saved.',
                'saved' => true,
            ]);
        });
    }

    public function removeSavedRecipe(Request $request, $recipe)
    {
        $userId = $request->user()->id;

        return DB::transaction(function () use ($userId, $recipe) {
            $recipeRecord = Recipe::where('id', $recipe)
                ->lockForUpdate()
                ->firstOrFail();

            $deleted = UsersSaveRecipe::where('users_id', $userId)
                ->where('recipes_id', $recipeRecord->id)
                ->delete();

            return response()->json([
                'message' => $deleted > 0
                    ? 'Recipe removed from saved recipes.'
                    : 'This recipe was not saved.',
                'saved' => false,
            ]);
        });
    }

    public function getSavedRecipes(Request $request)
    {
        $user_id = $request->user()->id;
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
            ->join('users_save_recipes', 'recipes.id', '=', 'users_save_recipes.recipes_id')
            ->where('users_save_recipes.users_id', $user_id)
            ->where('recipes.status', 1)
            ->orderBy('recipes.id')
            ->distinct()
            ->get();

            return $recipes;
    }

    private function generatePassword()
    {
        $input = '!#$%&?0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $strength = 10;
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[random_int(0, $input_length - 1)];
            $random_string .= $random_character;
        }
        return $random_string;
    }

    public function recoverPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $newPassword = $this->generatePassword();

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return response()->json([
            'message' => 'Password regenerated successfully',
            'password' => $newPassword,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
}
