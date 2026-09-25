<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database ER Diagram & Relational Dependency Visualizer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="module">
        import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
        mermaid.initialize({ startOnLoad: true, theme: 'forest' });
    </script>
    <style>
        body { background: #f4f6f9; }
        .dashboard-header { background: linear-gradient(135deg, #0d6efd, #6610f2); color: white; border-radius: 15px; }
        .nav-pills .nav-link.active { background-color: #0d6efd; font-weight: 600; }
        .nav-pills .nav-link { color: #495057; font-weight: 500; }
        .mermaid-container { background: white; border-radius: 15px; padding: 25px; overflow-x: auto; min-height: 400px; }
    </style>
</head>
<body>
    <div class="container-fluid py-4 px-lg-5">

        {{-- Header Navigation --}}
        <div class="dashboard-header p-4 mb-4 shadow-sm">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div>
                    <h1 class="fw-bold mb-1">🕸️ Database ER Diagram & Relational Dependency Visualizer</h1>
                    <p class="mb-0 opacity-75">Interactive Table Schema & Foreign Key Relationship Mapper</p>
                </div>
                <div class="mt-3 mt-lg-0 d-flex gap-2">
                    <a href="{{ route('reliese.er_diagram.data') }}" target="_blank" class="btn btn-warning fw-semibold">🧾 ER Data JSON</a>
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
                    <a class="nav-link active" href="{{ route('reliese.er_diagram') }}">🕸️ ER Diagram</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reliese.diff_sandbox') }}">⚡ Diff & Sandbox</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('reliese.generate') }}">⚙️ Generator</a>
                </li>
            </ul>
        </div>

        {{-- Summary Badges --}}
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted">Total Schema Tables</div>
                            <div class="fs-2 fw-bold text-primary">{{ count($erData['tables']) }} Tables</div>
                        </div>
                        <span class="fs-1">🗄️</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted">Detected Relationships / Foreign Keys</div>
                            <div class="fs-2 fw-bold text-success">{{ count($erData['relationships']) }} Relations</div>
                        </div>
                        <span class="fs-1">🔗</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Interactive Mermaid Diagram --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-bold fs-5 d-flex justify-content-between align-items-center">
                <span>📊 Interactive Entity-Relationship (ER) Diagram</span>
            </div>
            <div class="card-body text-center p-4">
                <div class="mermaid-container shadow-sm border rounded">
                    <pre class="mermaid">
{{ $erData['mermaid'] }}
                    </pre>
                </div>
            </div>
        </div>

        {{-- Table Relationship List --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 fw-bold fs-5">📋 Foreign Key Relationship Mappings</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Parent Model (One)</th>
                                <th>Parent Table</th>
                                <th>Child Model (Many)</th>
                                <th>Child Table</th>
                                <th>Foreign Key Column</th>
                                <th>Relation Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($erData['relationships'] as $rel)
                            <tr>
                                <td class="fw-bold text-primary">{{ $rel['from_model'] }}</td>
                                <td><code>{{ $rel['from_table'] }}</code></td>
                                <td class="fw-bold text-success">{{ $rel['to_model'] }}</td>
                                <td><code>{{ $rel['to_table'] }}</code></td>
                                <td><span class="badge bg-secondary font-monospace">{{ $rel['foreign_key'] }}</span></td>
                                <td><span class="badge bg-info text-dark">hasMany / belongsTo</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No explicit Foreign Key constraints found in current schema.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
