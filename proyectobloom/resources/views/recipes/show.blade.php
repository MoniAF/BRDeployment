@extends('recipes.layout')
  
@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Recipe Details</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-primary" href="{{ route('recipes.index') }}"> Back</a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <img class="img-fluid w-25" src="{{ asset('storage/imgs/'.$recipe->image) }}" alt="{{ $recipe->name }}">
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                {{ $recipe->name }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Description:</strong>
                {{ $recipe->description }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Preparation time:</strong>
                {{ $recipe->preparation_time }} minutes
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Cooking time:</strong>
                {{ $recipe->cooking_time }} minutes
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Total time:</strong>
                {{ $recipe->total_time }} minutes
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Instructions:</strong>
                <p style="white-space: pre-line;">{{ $recipe->preparation_instructions }}</p>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Portions:</strong>
                {{ $recipe->portions }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Category:</strong>
                {{ $recipe->category }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Occasion:</strong>
                {{ $recipe->occasion }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Level:</strong>
                {{ $recipe->level }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Likes:</strong>
                {{ $recipe->likes }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Ingredients:</strong>
                <ul>
                    @forelse ($ingredients as $ingredient)
                        <li>
                            {{ $ingredient->amount }}
                            {{ $ingredient->measurement_unit }}
                            {{ $ingredient->name }}
                        </li>
                    @empty
                        <li>No ingredients available.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection