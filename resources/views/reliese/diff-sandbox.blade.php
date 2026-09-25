<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Model Code Diff Inspector & Eloquent Sandbox Playground</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .dashboard-header { background: linear-gradient(135deg, #0d6efd, #6610f2); color: white; border-radius: 15px; }
        .code-box { background: #1e1e1e; color: #d4d4d4; font-family: monospace; font-size: 13px; max-height: 450px; overflow: auto; border-radius: 8px; padding: 15px; }
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
                    <h1 class="fw-bold mb-1">⚡ Model Code Diff Inspector & Eloquent Sandbox</h1>
                    <p class="mb-0 opacity-75">Side-by-Side Model Code Inspection & Live Query Sandbox</p>
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
                    <a class="nav-link" href="{{ route('reliese.configurator') }}">🛠️ Configurator Studio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reliese.er_diagram') }}">🕸️ ER Diagram</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('reliese.diff_sandbox') }}">⚡ Diff & Sandbox</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reliese.generate') }}">⚙️ Generator</a>
                </li>
            </ul>
        </div>

        {{-- Table Selector Form --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reliese.diff_sandbox') }}" class="row align-items-center g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Select Database Table for Diff Inspection</label>
                        <select name="table" class="form-select font-monospace" onchange="this.form.submit()">
                            @foreach($tables as $tbl)
                                <option value="{{ $tbl }}" {{ $selectedTable === $tbl ? 'selected' : '' }}>
                                    {{ $tbl }} ({{ \Illuminate\Support\Str::studly(\Illuminate\Support\Str::singular($tbl)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 pt-4">
                        <span class="badge bg-primary fs-6">Target Model: App\Models\{{ $diffData['model'] }}</span>
                    </div>
                </form>
            </div>
        </div>

        {{-- Side-by-Side Model Diff Inspector --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between">
                        <span>📦 Generated Base Model Code (app/Models/Base/{{ $diffData['model'] }}.php)</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="code-box mb-0">{{ $diffData['base_code'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 fw-bold d-flex justify-content-between">
                        <span>📄 Main Model Extension Code (app/Models/{{ $diffData['model'] }}.php)</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="code-box mb-0">{{ $diffData['main_code'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Eloquent Live Query Sandbox --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 fw-bold fs-5">🧪 Live Eloquent Query Sandbox Playground</div>
            <div class="card-body">
                <form id="sandboxForm" class="row g-3 mb-4">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Model Name</label>
                        <input type="text" id="sandboxModel" class="form-control font-monospace" value="{{ $diffData['model'] }}" placeholder="User">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Eager Load Relations (Comma Separated)</label>
                        <input type="text" id="sandboxWith" class="form-control font-monospace" placeholder="posts, roles">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Limit</label>
                        <input type="number" id="sandboxLimit" class="form-control" value="5" min="1" max="50">
                    </div>
                    <div class="col-md-2 pt-4">
                        <button type="submit" class="btn btn-success fw-bold w-100 py-2">▶ Run Query</button>
                    </div>
                </form>

                {{-- Sandbox Output Container --}}
                <div id="sandboxOutputContainer" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0">Query Output Response:</h6>
                        <span id="queryMetaBadge" class="badge bg-info text-dark"></span>
                    </div>
                    <pre id="sandboxOutput" class="bg-dark text-success p-3 rounded font-monospace" style="max-height: 350px; overflow: auto;"></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('sandboxForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const model = document.getElementById('sandboxModel').value;
            const withRelations = document.getElementById('sandboxWith').value;
            const limit = document.getElementById('sandboxLimit').value;

            const container = document.getElementById('sandboxOutputContainer');
            const output = document.getElementById('sandboxOutput');
            const badge = document.getElementById('queryMetaBadge');

            container.style.display = 'block';
            output.textContent = 'Executing query...';

            try {
                const response = await fetch('{{ route("reliese.sandbox.execute") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ model, with: withRelations, limit })
                });

                const data = await response.json();
                if (data.success) {
                    badge.textContent = `⚡ Executed in ${data.execution_time_ms} ms | ${data.count} Records`;
                    output.textContent = JSON.stringify(data.data, null, 2);
                } else {
                    badge.textContent = '❌ Query Error';
                    output.textContent = data.error;
                }
            } catch (err) {
                output.textContent = 'Error executing request: ' + err.message;
            }
        });
    </script>
</body>
</html>
