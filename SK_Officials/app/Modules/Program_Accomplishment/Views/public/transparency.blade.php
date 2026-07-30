<!DOCTYPE html>
<html lang="en">
<head>
    @include('layout::favicon')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Transparency - Program Accomplishment - SK Officials Portal</title>

    @vite([
        'app/Modules/Layout/css/header.css',
        'app/Modules/Layout/css/sidebar.css',
        'app/Modules/Program_Accomplishment/Assets/css/program-accomplishment.css',
    ])
    <link rel="stylesheet" href="{{ url('/shared/css/loading.css') }}">
</head>
<body>

@include('loading')
@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="page-container">

        <section class="page-header-section">
            <div class="page-header-left">
                <h1 class="page-title">Transparency</h1>
                <p class="page-subtitle">Annual program accomplishment statistics and budget utilization.</p>
            </div>
        </section>

        <section class="page-content-section">
            <div class="section-heading-row">
                <h2 class="section-title">Annual Overview</h2>
                <div class="filter-group">
                    <select id="yearFilter" class="filter-select">
                        @for($y = now()->year; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div class="stats-grid" id="statsGrid">
                <div class="stat-card">
                    <span class="stat-label">Completed Programs</span>
                    <span class="stat-value" id="statCompleted">0</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Total Budget Allocated</span>
                    <span class="stat-value" id="statAllocated">&#8369;0.00</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Total Actual Expense</span>
                    <span class="stat-value" id="statExpense">&#8369;0.00</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Remaining Budget</span>
                    <span class="stat-value" id="statRemaining">&#8369;0.00</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Budget Utilization</span>
                    <span class="stat-value" id="statUtilization">0%</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Total Beneficiaries</span>
                    <span class="stat-value" id="statBeneficiaries">0</span>
                </div>
            </div>

            <div class="section-heading-row" style="margin-top:2rem;">
                <h2 class="section-title">Completed Programs</h2>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date Completed</th>
                                <th>Allocated</th>
                                <th>Expense</th>
                                <th>Remaining</th>
                                <th>Utilization</th>
                                <th>Participants</th>
                            </tr>
                        </thead>
                        <tbody id="transparencyTableBody">
                            <tr><td colspan="7" class="empty-state">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

@vite([
    'app/Modules/Layout/js/header.js',
    'app/Modules/Layout/js/sidebar.js',
    'app/Modules/Program_Accomplishment/Assets/js/program-accomplishment.js',
])
<script src="{{ url('/shared/js/loading.js') }}"></script>
</body>
</html>
