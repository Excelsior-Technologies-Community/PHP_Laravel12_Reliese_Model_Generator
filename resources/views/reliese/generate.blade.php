<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Reliese Model Generation Manager</title>

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
        🔄 Model Generation Manager
    </h1>

    <p class="text-muted mb-4">

        Generate Reliese models directly from the Laravel dashboard.

    </p>

    @if($message)

        <div class="alert alert-success">

            <strong>Success:</strong>
            {{ $message }}

        </div>

    @endif

    @if($error)

        <div class="alert alert-danger">

            <strong>Error:</strong>
            {{ $error }}

        </div>

    @endif

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <h4>
                Generate All Models
            </h4>

            <p class="text-muted">

                Runs:

                <code>
                    php artisan code:models
                </code>

            </p>

            <form method="POST"
                action="{{ route('reliese.generate') }}">

                @csrf

                <button
                    type="submit"
                    class="btn btn-success">

                    🔄 Generate All Models

                </button>

            </form>

        </div>

    </div>

    <div class="card shadow-sm border-0">

        <div class="card-header bg-dark text-white">

            Generate Specific Table

        </div>

        <div class="card-body">

            <form method="POST"
                action="{{ route('reliese.generate') }}">

                @csrf

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
                            type="submit"
                            class="btn btn-primary w-100">

                            Generate Model

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    @if($output)

        <div class="card shadow-sm border-0 mt-4">

            <div class="card-header">
                Artisan Output
            </div>

            <div class="card-body">

                <pre class="bg-dark text-white p-3 rounded mb-0"
                    style="white-space: pre-wrap;">{{ $output }}</pre>

            </div>

        </div>

    @endif

    <div class="alert alert-warning mt-4">

        <strong>Important:</strong>

        Reliese regenerates files inside
        <code>app/Models/Base/</code>.

        Keep custom application logic inside the main
        <code>app/Models/</code> classes.

    </div>

</div>

</body>
</html>