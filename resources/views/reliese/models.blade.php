<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Reliese Model Explorer</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark">

        <div class="container">

            <a
                class="navbar-brand fw-bold"
                href="{{ route('reliese.dashboard') }}">

                Reliese Model Generator

            </a>

            <div class="d-flex gap-2">

                <a
                    href="{{ route('reliese.generate') }}"
                    class="btn btn-outline-light btn-sm">

                    Generate

                </a>

                <a
                    href="{{ route('reliese.compare') }}"
                    class="btn btn-outline-light btn-sm">

                    Compare

                </a>

            </div>

        </div>

    </nav>


    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold">
                    📊 Reliese Model Explorer
                </h1>

                <p class="text-muted">

                    Search, filter and inspect generated models.

                </p>

            </div>

            <div>

                <a
                    href="{{ route('reliese.export.csv') }}"
                    class="btn btn-outline-success">

                    CSV

                </a>

                <a
                    href="{{ route('reliese.export.json') }}"
                    class="btn btn-outline-primary">

                    JSON

                </a>

            </div>

        </div>


        {{-- Filters --}}

        <form
            method="GET"
            action="{{ route('reliese.models') }}"
            class="card card-body shadow-sm border-0 mb-4">

            <div class="row g-3">

                {{-- Search --}}

                <div class="col-md-4">

                    <label class="form-label fw-bold">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Table or model name...">

                </div>


                {{-- Model status --}}

                <div class="col-md-2">

                    <label class="form-label fw-bold">
                        Model Status
                    </label>

                    <select
                        name="status"
                        class="form-select">

                        <option
                            value="all"
                            @selected($status==='all' )>

                            All

                        </option>

                        <option
                            value="generated"
                            @selected($status==='generated' )>

                            Generated

                        </option>

                        <option
                            value="missing"
                            @selected($status==='missing' )>

                            Missing

                        </option>

                    </select>

                </div>


                {{-- Schema status --}}

                <div class="col-md-2">

                    <label class="form-label fw-bold">
                        Schema
                    </label>

                    <select
                        name="schema"
                        class="form-select">

                        <option
                            value="all"
                            @selected($schema==='all' )>

                            All

                        </option>

                        <option
                            value="synced"
                            @selected($schema==='synced' )>

                            Synced

                        </option>

                        <option
                            value="check"
                            @selected($schema==='check' )>

                            Needs Check

                        </option>

                    </select>

                </div>


                {{-- Sort --}}

                <div class="col-md-2">

                    <label class="form-label fw-bold">
                        Sort By
                    </label>

                    <select
                        name="sort"
                        class="form-select">

                        <option
                            value="table"
                            @selected($sort==='table' )>

                            Table

                        </option>

                        <option
                            value="model"
                            @selected($sort==='model' )>

                            Model

                        </option>

                        <option
                            value="column_count"
                            @selected($sort==='column_count' )>

                            Columns

                        </option>

                        <option
                            value="relationship_count"
                            @selected($sort==='relationship_count' )>

                            Relationships

                        </option>

                    </select>

                </div>


                {{-- Direction --}}

                <div class="col-md-2">

                    <label class="form-label fw-bold">
                        Direction
                    </label>

                    <select
                        name="direction"
                        class="form-select">

                        <option
                            value="asc"
                            @selected($direction==='asc' )>

                            Ascending

                        </option>

                        <option
                            value="desc"
                            @selected($direction==='desc' )>

                            Descending

                        </option>

                    </select>

                </div>


                <div class="col-md-12 d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        🔎 Apply Filters

                    </button>

                    <a
                        href="{{ route('reliese.models') }}"
                        class="btn btn-outline-secondary">

                        Reset

                    </a>

                </div>

            </div>

        </form>


        {{-- Result count --}}

        <div class="alert alert-info">

            Showing
            <strong>{{ $modelData->count() }}</strong>
            of
            <strong>{{ $modelData->total() }}</strong>
            models.

        </div>


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

                    {{-- Columns --}}

                    <div class="col-md-6">

                        <h5>
                            Database Columns
                        </h5>

                        <div class="table-responsive">

                            <table
                                class="table table-sm table-bordered">

                                <thead>

                                    <tr>

                                        <th>
                                            Column
                                        </th>

                                        <th>
                                            Type
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach($model['columns'] as $column)

                                    <tr>

                                        <td>
                                            {{ $column['name'] }}
                                        </td>

                                        <td>

                                            <span
                                                class="badge bg-secondary">

                                                {{ $column['type'] }}

                                            </span>

                                        </td>

                                    </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>


                    {{-- Relationships --}}

                    <div class="col-md-6">

                        <h5>
                            Relationships
                        </h5>

                        @if(count($model['relationships']))

                        @foreach($model['relationships'] as $relationship)

                        <span
                            class="badge bg-info text-dark me-1 mb-1">

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

                        <table
                            class="table table-sm">

                            <tbody>

                                @foreach($model['casts'] as $field => $cast)

                                <tr>

                                    <td>
                                        {{ $field }}
                                    </td>

                                    <td>

                                        <span
                                            class="badge bg-secondary">

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

                <span
                    class="badge bg-primary me-1 mb-1">

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

                <div
                    class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div>

                        @if($model['schema_match'])

                        <span class="badge bg-success">
                            ✓ Schema Synced
                        </span>

                        @else

                        <span
                            class="badge bg-warning text-dark">

                            ⚠ Schema Needs Check

                        </span>

                        @endif

                    </div>


                    <div class="d-flex gap-2">

                        <a
                            href="{{ route('reliese.compare', [
                                'table' => $model['table']
                            ]) }}"
                            class="btn btn-sm btn-outline-warning">

                            Compare

                        </a>


                        <form
                            method="POST"
                            action="{{ route('reliese.generate') }}">

                            @csrf

                            <input
                                type="hidden"
                                name="table"
                                value="{{ $model['table'] }}">

                            <button
                                type="submit"
                                class="btn btn-sm btn-outline-success">

                                🔄 Generate

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

        @empty

        <div class="alert alert-warning">

            No models match your search/filter criteria.

        </div>

        @endforelse


        {{-- Number-only pagination --}}

        @if($modelData->hasPages())

        <div class="d-flex justify-content-center mt-4">

            {{ $modelData->onEachSide(1)->links('pagination::bootstrap-5') }}

        </div>

        @endif


    </div>

</body>

</html>