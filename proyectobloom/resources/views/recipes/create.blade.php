@extends('recipes.layout')

@section('title', 'Add Recipe | Bloom Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2 mb-0">Add New Recipe</h1>

    <a class="btn btn-outline-secondary" href="{{ route('recipes.index') }}">
        Back
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please correct the following errors:</strong>

        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form
    action="{{ route('recipes.store') }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">General information</h2>

            <div class="mb-3">
                <label for="recipe_image" class="form-label">
                    Recipe image
                </label>

                <input
                    id="recipe_image"
                    type="file"
                    name="recipe_image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp"
                    required
                >

                <img
                    id="preview"
                    class="img-thumbnail mt-3 d-none"
                    width="160"
                    height="160"
                    alt="Recipe preview"
                >
            </div>

            <div class="mb-3">
                <label for="recipe" class="form-label">Recipe name</label>

                <input
                    id="recipe"
                    type="text"
                    name="recipe"
                    value="{{ old('recipe') }}"
                    class="form-control"
                    required
                >
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="categories_id" class="form-label">Category</label>

                    <select
                        id="categories_id"
                        name="categories_id"
                        class="form-select"
                        required
                    >
                        @foreach ($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(old('categories_id') == $category->id)
                            >
                                {{ $category->category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="occasions_id" class="form-label">Occasion</label>

                    <select
                        id="occasions_id"
                        name="occasions_id"
                        class="form-select"
                        required
                    >
                        @foreach ($occasions as $occasion)
                            <option
                                value="{{ $occasion->id }}"
                                @selected(old('occasions_id') == $occasion->id)
                            >
                                {{ $occasion->occasion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="levels_id" class="form-label">Level</label>

                    <select
                        id="levels_id"
                        name="levels_id"
                        class="form-select"
                        required
                    >
                        @foreach ($levels as $level)
                            <option
                                value="{{ $level->id }}"
                                @selected(old('levels_id') == $level->id)
                            >
                                {{ $level->level }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="preparation_time" class="form-label">
                        Preparation time
                    </label>

                    <input
                        id="preparation_time"
                        type="number"
                        name="preparation_time"
                        value="{{ old('preparation_time') }}"
                        class="form-control"
                        min="0"
                        required
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label for="cooking_time" class="form-label">
                        Cooking time
                    </label>

                    <input
                        id="cooking_time"
                        type="number"
                        name="cooking_time"
                        value="{{ old('cooking_time') }}"
                        class="form-control"
                        min="0"
                        required
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label for="portions" class="form-label">Portions</label>

                    <input
                        id="portions"
                        type="number"
                        name="portions"
                        value="{{ old('portions') }}"
                        class="form-control"
                        min="1"
                        required
                    >
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>

                <textarea
                    id="description"
                    name="description"
                    class="form-control"
                    rows="4"
                    required
                >{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="preparation_instructions" class="form-label">
                    Instructions
                </label>

                <textarea
                    id="preparation_instructions"
                    name="preparation_instructions"
                    class="form-control"
                    rows="6"
                    required
                >{{ old('preparation_instructions') }}</textarea>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Ingredients</h2>

                <button
                    type="button"
                    id="add-ingredient"
                    class="btn btn-success btn-sm"
                >
                    Add ingredient
                </button>
            </div>

            <div id="ingredients">
                <div class="ingredient-row row g-2 mb-2">
                    <div class="col-md-5">
                        <input
                            type="text"
                            name="ingredients[]"
                            class="form-control"
                            placeholder="Ingredient name"
                            required
                        >
                    </div>

                    <div class="col-md-3">
                        <input
                            type="text"
                            name="amounts[]"
                            class="form-control"
                            placeholder="Amount (optional)"
                        >
                    </div>

                    <div class="col-md-3">
                        <select name="units[]" class="form-select" required>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">
                                    {{ $unit->measurement_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button
                            type="button"
                            class="btn btn-outline-danger remove-ingredient"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        Save recipe
    </button>
</form>

<template id="ingredient-template">
    <div class="ingredient-row row g-2 mb-2">
        <div class="col-md-5">
            <input
                type="text"
                name="ingredients[]"
                class="form-control"
                placeholder="Ingredient name"
                required
            >
        </div>

        <div class="col-md-3">
            <input
                type="text"
                name="amounts[]"
                class="form-control"
                placeholder="Amount (optional)"
            >
        </div>

        <div class="col-md-3">
            <select name="units[]" class="form-select" required>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}">
                        {{ $unit->measurement_unit }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-1">
            <button
                type="button"
                class="btn btn-outline-danger remove-ingredient"
            >
                ×
            </button>
        </div>
    </div>
</template>

<script>
    const imageInput = document.querySelector('#recipe_image');
    const preview = document.querySelector('#preview');
    const ingredientsContainer = document.querySelector('#ingredients');
    const ingredientTemplate = document.querySelector('#ingredient-template');

    imageInput.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) {
            preview.classList.add('d-none');
            preview.removeAttribute('src');
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    });

    document.querySelector('#add-ingredient').addEventListener('click', function () {
        const newIngredient = ingredientTemplate.content.cloneNode(true);
        ingredientsContainer.appendChild(newIngredient);
    });

    ingredientsContainer.addEventListener('click', function (event) {
        if (!event.target.classList.contains('remove-ingredient')) {
            return;
        }

        const rows = ingredientsContainer.querySelectorAll('.ingredient-row');

        if (rows.length > 1) {
            event.target.closest('.ingredient-row').remove();
        }
    });
</script>
@endsection