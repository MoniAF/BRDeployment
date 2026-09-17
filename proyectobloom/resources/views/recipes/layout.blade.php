<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'Bloom Admin')</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    </head>
    <body class="bg-light">
        @auth
            @if ((int) auth()->user()->profiles_id === 1)
                <nav class="navbar bg-white border-bottom shadow-sm">
                    <div class="container d-flex flex-wrap gap-3">
                        <a class="navbar-brand fw-bold" href="{{ route('recipes.index') }}">
                            Bloom Admin
                        </a>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <a class="btn btn-outline-secondary btn-sm"
                            href="{{ route('recipes.index') }}">
                                Recipes
                            </a>

                            <a class="btn btn-outline-secondary btn-sm"
                            href="{{ route('recipes.create') }}">
                                Add recipe
                            </a>

                            <a class="btn btn-outline-secondary btn-sm"
                            href="{{ route('recipes.unpublish') }}">
                                Unpublished
                            </a>

                            <form method="POST"
                                action="{{ route('admin.logout') }}"
                                class="m-0">
                                @csrf

                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    Log out
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>
            @endif
        @endauth

        <main class="container py-4">
            @yield('content')
        </main>
    </body>
</html>
