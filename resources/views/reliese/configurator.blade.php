<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reliese Custom Configurator Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .dashboard-header { background: linear-gradient(135deg, #0d6efd, #6610f2); color: white; border-radius: 15px; }
        .config-card { border: 0; border-radius: 15px; }
        .nav-pills .nav-link.active { background-color: #0d6efd; font-weight: 600; }
        .nav-pills .nav-link { color: #495057; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container-fluid py-4 px-lg-5">

        {{-- Header Navigation --}}
        <div class="dashboard-header p-4 mb-4 shadow-sm">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div>
                    <h1 class="fw-bold mb-1">🛠️ Reliese Custom Configurator Studio</h1>
                    <p class="mb-0 opacity-75">Live GUI Configurator for Reliese Eloquent Model Generator</p>
                </div>
                <div class="mt-3 mt-lg-0">
                    <a href="{{ route('reliese.dashboard') }}" class="btn btn-light fw-semibold">⬅️ Dashboard</a>
                </div>
            </div>

            {{-- Top Navigation Pills --}}
            <ul class="nav nav-pills mt-4 bg-white p-2 rounded-3 shadow-sm">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reliese.dashboard') }}">📊 Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reliese.models') }}">🔍 Model Explorer</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('reliese.configurator') }}">🛠️ Configurator Studio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reliese.er_diagram') }}">🕸️ ER Diagram</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reliese.diff_sandbox') }}">⚡ Diff & Sandbox</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reliese.generate') }}">⚙️ Generator</a>
                </li>
            </ul>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row g-4">
            {{-- Configurator Form --}}
            <div class="col-lg-6">
                <div class="card config-card shadow-sm h-100">
                    <div class="card-header bg-white py-3 fw-bold fs-5">⚙️ Reliese Models Configuration Settings</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('reliese.configurator.update') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Target Model Namespace</label>
                                <input type="text" name="namespace" class="form-control font-monospace" value="{{ $currentConfig['namespace'] }}" placeholder="App\Models">
                                <small class="text-muted">Directory where Eloquent models will be generated.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Parent Base Model Inheritance</label>
                                <input type="text" name="parent" class="form-control font-monospace" value="{{ $currentConfig['parent'] }}" placeholder="Illuminate\Database\Eloquent\Model">
                                <small class="text-muted">Base class extended by generated Eloquent models.</small>
                            </div>

                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="use_soft_deletes" id="softDeletesCheck" {{ $currentConfig['use_soft_deletes'] ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="softDeletesCheck">Enable SoftDeletes Trait Auto-Detection</label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Excluded Tables (Comma Separated)</label>
                                <input type="text" name="except" class="form-control font-monospace" value="{{ $currentConfig['except'] }}" placeholder="migrations, failed_jobs, password_reset_tokens">
                                <small class="text-muted">Tables to skip during model generation.</small>
                            </div>

                            <button type="submit" class="btn btn-primary fw-bold px-4 py-2 mt-2">💾 Save Configuration Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Raw Config Code Preview --}}
            <div class="col-lg-6">
                <div class="card config-card shadow-sm h-100">
                    <div class="card-header bg-white py-3 fw-bold fs-5">📄 Generated config/models.php Stub Code</div>
                    <div class="card-body p-0">
                        <pre class="bg-dark text-light p-3 rounded-bottom mb-0 font-monospace" style="min-height: 380px;">{{ $rawConfig }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
