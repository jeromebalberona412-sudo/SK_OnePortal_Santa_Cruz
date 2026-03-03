<!DOCTYPE html>
<html lang="en">
<head>
  <title>SK Officials Dashboard</title>

    @vite([
        'app/modules/layout/css/header.css',
        'app/modules/layout/css/sidebar.css',
        'app/modules/Dashboard/assets/css/dashboard.css'
    ])
</head>
<body>

<!-- ================= HEADER ================= -->
@include('layout::header')

<!-- ================= SIDEBAR ================= -->
@include('layout::sidebar')

<!-- ================= MAIN CONTENT ================= -->
<main class="main-content">
    <div class="dashboard-container">
        <div class="welcome-section">
            <h2>Hello World</h2>
            <p>Welcome to the SK Officials Dashboard</p>
        </div>
    </div>
</main>

@vite([
    'app/modules/layout/js/header.js',
    'app/modules/layout/js/sidebar.js',
    'app/modules/Dashboard/assets/js/dashboard.js'
])

</body>
</html>