<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Reliese Schema Comparison</title>

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

    <h1 class="fw-bold">
        🔎 Model & Database Schema Comparison
    </h1>

    <p class="text-muted mb-4">

        Compare the current database table structure with
        the generated Reliese model.

    </p>

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form method="GET"
                action="{{ route('reliese.compare') }}">

                <div class="row g-3">

                    <div class="col-md-9">

                        <select
                            name="table"
                            class="form-select"
                            required>

                            <option value="">
                                Select database table
                            </option>

                            @foreach($tables as $table)

                                <option
                                    value="{{ $table }}"
                                    @selected($selectedTable === $table)>

                                    {{ $table }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-3">

                        <button
                            class="btn btn-primary w-100">

                            Compare

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    @if($comparison)

        @if(!$comparison['model_exists'])

            <div class="alert alert-danger">

                <strong>Model Missing</strong>

                <p class="mb-0">

                    Reliese base model
                    <code>
                        {{ $comparison['model'] }}
                    </code>
                    was not found.

                </p>

            </div>

        @elseif($comparison['matched'])

            <div class="alert alert-success">

                <strong>✓ Schema Matched</strong>

                <p class="mb-0">

                    Database columns and generated model
                    properties are synchronized.

                </p>

            </div>

        @else

            <div class="alert alert-warning">

                <strong>⚠ Schema Difference Detected</strong>

                The database and generated model should be
                regenerated/checked.

            </div>

        @endif

        <div class="row g-4">

            <div class="col-md-6">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-header bg-primary text-white">

                        Database Columns

                    </div>

                    <div class="card-body">

                        @foreach($comparison['database_columns'] as $column)

                            @if(in_array(
                                $column,
                                $comparison['missing_from_model']
                            ))

                                <div class="alert alert-danger py-2">

                                    ❌ {{ $column }}

                                    <small class="d-block">
                                        Missing from generated model
                                    </small>

                                </div>

                            @else

                                <div class="alert alert-success py-2">

                                    ✓ {{ $column }}

                                </div>

                            @endif

                        @endforeach

                    </div>

                </div>

            </div>

            <div class="col-md-6">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-header bg-dark text-white">

                        Reliese Model Properties

                    </div>

                    <div class="card-body">

                        @foreach($comparison['model_columns'] as $column)

                            @if(in_array(
                                $column,
                                $comparison['missing_from_database']
                            ))

                                <div class="alert alert-warning py-2">

                                    ⚠ {{ $column }}

                                    <small class="d-block">
                                        Not found in database
                                    </small>

                                </div>

                            @else

                                <div class="alert alert-success py-2">

                                    ✓ {{ $column }}

                                </div>

                            @endif

                        @endforeach

                    </div>

                </div>

            </div>

        </div>

        <div class="card shadow-sm border-0 mt-4">

            <div class="card-header">

                Relationship Information

            </div>

            <div class="card-body">

                @if(count($comparison['relationships']))

                    @foreach($comparison['relationships'] as $relationship)

                        <span class="badge bg-info text-dark me-2">

                            {{ $relationship }}

                        </span>

                    @endforeach

                @else

                    <span class="text-muted">

                        No relationships detected.

                    </span>

                @endif

            </div>

        </div>

        <div class="card shadow-sm border-0 mt-4">

            <div class="card-header">

                Generated Casts

            </div>

            <div class="card-body">

                @if(count($comparison['casts']))

                    <div class="table-responsive">

                        <table class="table">

                            <thead>

                            <tr>
                                <th>Column</th>
                                <th>Cast</th>
                            </tr>

                            </thead>

                            <tbody>

                            @foreach($comparison['casts'] as $column => $cast)

                                <tr>

                                    <td>
                                        {{ $column }}
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

                    </div>

                @else

                    <span class="text-muted">
                        No casts detected.
                    </span>

                @endif

            </div>

        </div>

    @endif

</div>

</body>
</html>