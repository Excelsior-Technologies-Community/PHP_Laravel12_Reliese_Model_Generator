<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Reliese Model Explorer</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a class="navbar-brand fw-bold"
            href="{{ route('reliese.dashboard') }}">
            Reliese Model Generator
        </a>

        <a href="{{ route('reliese.dashboard') }}"
            class="btn btn-outline-light btn-sm">
            Dashboard
        </a>

    </div>

</nav>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="fw-bold">
                📊 Reliese Model Explorer
            </h1>

            <p class="text-muted">
                Explore generated models and their database metadata.
            </p>

        </div>

    </div>

    <form method="GET"
        action="{{ route('reliese.models') }}"
        class="card card-body shadow-sm border-0 mb-4">

        <div class="row g-2">

            <div class="col-md-10">

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="Search table or model name...">

            </div>

            <div class="col-md-2">

                <button class="btn btn-primary w-100">
                    🔎 Search
                </button>

            </div>

        </div>

    </form>

    @forelse($modelData as $model)

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-dark text-white">

                <div class="d-flex justify-content-between">

                    <div>

                        <strong>
                            {{ $model['model'] }}
                        </strong>

                        <span class="text-white-50">
                            → {{ $model['table'] }}
                        </span>

                    </div>

                    @if($model['exists'])

                        <span class="badge bg-success">
                            Generated
                        </span>

                    @else

                        <span class="badge bg-danger">
                            Missing
                        </span>

                    @endif

                </div>

            </div>

            <div class="card-body">

                <div class="row g-4">

                    <div class="col-md-6">

                        <h5>
                            Database Columns
                        </h5>

                        <div class="table-responsive">

                            <table class="table table-sm table-bordered">

                                <thead>

                                <tr>
                                    <th>Column</th>
                                    <th>Type</th>
                                </tr>

                                </thead>

                                <tbody>

                                @foreach($model['columns'] as $column)

                                    <tr>

                                        <td>
                                            {{ $column['name'] }}
                                        </td>

                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $column['type'] }}
                                            </span>
                                        </td>

                                    </tr>

                                @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                    <div class="col-md-6">

                        <h5>
                            Relationships
                        </h5>

                        @if(count($model['relationships']))

                            @foreach($model['relationships'] as $relationship)

                                <span class="badge bg-info text-dark me-1 mb-1">

                                    {{ $relationship }}

                                </span>

                            @endforeach

                        @else

                            <p class="text-muted">
                                No relationships detected.
                            </p>

                        @endif

                        <hr>

                        <h5>
                            Casts
                        </h5>

                        @if(count($model['casts']))

                            <table class="table table-sm">

                                <tbody>

                                @foreach($model['casts'] as $field => $cast)

                                    <tr>

                                        <td>
                                            {{ $field }}
                                        </td>

                                        <td>
                                            <span class="badge bg-secondary">
                                                {{ $cast }}
                                            </span>
                                        </td>

                                    </tr>

                                @endforeach

                                </tbody>

                            </table>

                        @else

                            <p class="text-muted">
                                No casts detected.
                            </p>

                        @endif

                    </div>

                </div>

                <hr>

                <h5>
                    Fillable Fields
                </h5>

                @if(count($model['fillable']))

                    @foreach($model['fillable'] as $field)

                        <span class="badge bg-primary me-1 mb-1">
                            {{ $field }}
                        </span>

                    @endforeach

                @else

                    <span class="text-muted">
                        No fillable fields detected.
                    </span>

                @endif

            </div>

            <div class="card-footer bg-white">

                <div class="d-flex justify-content-between">

                    <small class="text-muted">

                        Base:
                        <code>
                            app/Models/Base/{{ $model['model'] }}.php
                        </code>

                    </small>

                    <a
                        href="{{ route('reliese.compare', ['table' => $model['table']]) }}"
                        class="btn btn-sm btn-outline-warning">

                        Compare Schema

                    </a>

                </div>

            </div>

        </div>

    @empty

        <div class="alert alert-info">

            No models found.

        </div>

    @endforelse

</div>

</body>
</html>