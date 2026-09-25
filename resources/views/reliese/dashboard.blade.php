<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Reliese Model Generator Dashboard</title>

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
                    href="{{ route('reliese.models') }}"
                    class="btn btn-outline-light btn-sm">

                    Model Explorer

                </a>

                <a
                    href="{{ route('reliese.configurator') }}"
                    class="btn btn-outline-light btn-sm">

                    Configurator

                </a>

                <a
                    href="{{ route('reliese.er_diagram') }}"
                    class="btn btn-outline-light btn-sm">

                    ER Diagram

                </a>

                <a
                    href="{{ route('reliese.diff_sandbox') }}"
                    class="btn btn-outline-light btn-sm">

                    Diff & Sandbox

                </a>

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

        {{-- Success message --}}

        @if(session('success'))

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert">

            <strong>✓ Success</strong>

            <div>
                {{ session('success') }}
            </div>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>

        @endif


        <div class="mb-4">

            <h1 class="fw-bold">

                Reliese Model Generator Dashboard

            </h1>

            <p class="text-muted">

                Monitor database tables, generated models,
                relationships and schema synchronization.

            </p>

        </div>


        {{-- Statistics --}}

        <div class="row g-4 mb-5">

            <div class="col-md-3">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-body">

                        <h6 class="text-muted">
                            Database Tables
                        </h6>

                        <h2 class="fw-bold">
                            {{ $totalTables }}
                        </h2>

                        <small class="text-muted">
                            Tables detected
                        </small>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-body">

                        <h6 class="text-muted">
                            Generated Models
                        </h6>

                        <h2 class="fw-bold text-success">
                            {{ $totalModels }}
                        </h2>

                        <small class="text-muted">
                            Reliese models
                        </small>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-body">

                        <h6 class="text-muted">
                            Relationships
                        </h6>

                        <h2 class="fw-bold text-primary">
                            {{ $totalRelationships }}
                        </h2>

                        <small class="text-muted">
                            Detected relationships
                        </small>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-body">

                        <h6 class="text-muted">
                            Schema Synced
                        </h6>

                        <h2 class="fw-bold text-success">
                            {{ $syncedModels }}
                        </h2>

                        <small class="text-muted">
                            Models synchronized
                        </small>

                    </div>

                </div>

            </div>

        </div>


        {{-- Additional statistics --}}

        <div class="row g-4 mb-5">

            <div class="col-md-6">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <h5 class="fw-bold">
                            Missing Models
                        </h5>

                        <h2 class="text-danger">
                            {{ $missingModels }}
                        </h2>

                        <a
                            href="{{ route('reliese.models', [
                            'status' => 'missing'
                        ]) }}"
                            class="btn btn-outline-danger btn-sm">

                            View Missing Models

                        </a>

                    </div>

                </div>

            </div>


            <div class="col-md-6">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <h5 class="fw-bold">
                            Out-of-Sync Models
                        </h5>

                        <h2 class="text-warning">
                            {{ $outOfSyncModels }}
                        </h2>

                        <a
                            href="{{ route('reliese.models', [
                            'schema' => 'check'
                        ]) }}"
                            class="btn btn-outline-warning btn-sm">

                            View Schema Issues

                        </a>

                    </div>

                </div>

            </div>

        </div>


        {{-- Main actions --}}

        <div class="row g-4 mb-5">

            <div class="col-md-4">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-body">

                        <h4>
                            📊 Model Explorer
                        </h4>

                        <p class="text-muted">

                            Search, filter, sort and paginate
                            generated models.

                        </p>

                        <a
                            href="{{ route('reliese.models') }}"
                            class="btn btn-primary">

                            Open Explorer

                        </a>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-body">

                        <h4>
                            🔄 Generation Manager
                        </h4>

                        <p class="text-muted">

                            Generate one model or all models
                            from the dashboard.

                        </p>

                        <a
                            href="{{ route('reliese.generate') }}"
                            class="btn btn-success">

                            Manage Generation

                        </a>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card shadow-sm border-0 h-100">

                    <div class="card-body">

                        <h4>
                            🔎 Schema Comparison
                        </h4>

                        <p class="text-muted">

                            Compare database columns against
                            generated Reliese models.

                        </p>

                        <a
                            href="{{ route('reliese.compare') }}"
                            class="btn btn-warning">

                            Compare Schema

                        </a>

                    </div>

                </div>

            </div>

        </div>


        {{-- Export --}}

        <div class="card shadow-sm border-0 mb-5">

            <div class="card-body">

                <h5 class="fw-bold">
                    📥 Export Reports
                </h5>

                <p class="text-muted">

                    Download the complete model generation report.

                </p>

                <a
                    href="{{ route('reliese.export.csv') }}"
                    class="btn btn-outline-success me-2">

                    📥 Export CSV

                </a>

                <a
                    href="{{ route('reliese.export.json') }}"
                    class="btn btn-outline-primary">

                    📥 Export JSON

                </a>

            </div>

        </div>


        {{-- Table --}}

        <div class="card shadow-sm border-0">

            <div class="card-header bg-dark text-white">

                Database Tables & Model Status

            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead class="table-light">

                            <tr>

                                <th>#</th>

                                <th>Table</th>

                                <th>Model</th>

                                <th>Columns</th>

                                <th>Relationships</th>

                                <th>Model Status</th>

                                <th>Schema</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($modelData as $index => $model)

                            <tr>

                                <td>
                                    {{ $index + 1 }}
                                </td>

                                <td>
                                    <strong>
                                        {{ $model['table'] }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $model['model'] }}
                                </td>

                                <td>
                                    {{ $model['column_count'] }}
                                </td>

                                <td>
                                    {{ $model['relationship_count'] }}
                                </td>

                                <td>

                                    @if($model['exists'])

                                    <span class="badge bg-success">
                                        Generated
                                    </span>

                                    @else

                                    <span class="badge bg-danger">
                                        Missing
                                    </span>

                                    @endif

                                </td>

                                <td>

                                    @if($model['schema_match'])

                                    <span class="badge bg-success">
                                        Synced
                                    </span>

                                    @else

                                    <span
                                        class="badge bg-warning text-dark">

                                        Check

                                    </span>

                                    @endif

                                </td>

                            </tr>

                            @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center py-4">

                                    No database tables found.

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>