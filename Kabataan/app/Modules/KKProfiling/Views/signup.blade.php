<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - KK Profiling - SK OnePortal</title>
    @vite([
        'app/Modules/Homepage/assets/css/homepage.css',
        'app/Modules/Shared/assets/css/loading.css',
        'app/Modules/Shared/assets/js/loading.js',
    ])
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            padding: 2rem;
        }
        .signup-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 3rem;
        }
        .signup-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .signup-header img {
            height: 48px;
            margin-bottom: 1rem;
        }
        .signup-header h1 {
            font-size: 1.75rem;
            color: #1a1a1a;
            margin-bottom: 0.5rem;
        }
        .signup-header p {
            color: #666;
            font-size: 0.95rem;
        }
        .barangay-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .barangay-btn {
            padding: 1.25rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.95rem;
            font-weight: 500;
            color: #333;
            text-align: center;
        }
        .barangay-btn:hover {
            border-color: #667eea;
            background: #f5f7ff;
            transform: translateY(-2px);
        }
        .barangay-btn:active {
            transform: translateY(0);
        }
        .barangay-search {
            margin-bottom: 1.5rem;
        }
        .barangay-search input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        .barangay-search input:focus {
            outline: none;
            border-color: #667eea;
        }
        .no-results {
            text-align: center;
            padding: 2rem 1rem;
            color: #999;
        }
    </style>
</head>
<body>
    @include('dashboard::loading')

    <div class="signup-container">
        <div class="signup-header">
            <img src="/images/skoneportal_logo.webp" alt="SK OnePortal">
            <h1>KK Profiling Sign Up</h1>
            <p>Select your barangay to get started</p>
        </div>

        <div class="barangay-search">
            <input 
                type="text" 
                id="barangaySearch" 
                placeholder="Search barangay..."
                aria-label="Search barangay"
            >
        </div>

        <div class="barangay-grid" id="barangayGrid">
            @php
            $barangays = [
                ['slug' => 'alipit', 'name' => 'Alipit'],
                ['slug' => 'bagumbayan', 'name' => 'Bagumbayan'],
                ['slug' => 'barangay-i-poblacion-i', 'name' => 'Barangay I (Poblacion I)'],
                ['slug' => 'barangay-ii-poblacion-ii', 'name' => 'Barangay II (Poblacion II)'],
                ['slug' => 'barangay-iii-poblacion-iii', 'name' => 'Barangay III (Poblacion III)'],
                ['slug' => 'barangay-iv-poblacion-iv', 'name' => 'Barangay IV (Poblacion IV)'],
                ['slug' => 'barangay-v-poblacion-v', 'name' => 'Barangay V (Poblacion V)'],
                ['slug' => 'bubukal', 'name' => 'Bubukal'],
                ['slug' => 'calios', 'name' => 'Calios'],
                ['slug' => 'duhat', 'name' => 'Duhat'],
                ['slug' => 'gatid', 'name' => 'Gatid'],
                ['slug' => 'jasaan', 'name' => 'Jasaan'],
                ['slug' => 'labuin', 'name' => 'Labuin'],
                ['slug' => 'malinao', 'name' => 'Malinao'],
                ['slug' => 'oogong', 'name' => 'Oogong'],
                ['slug' => 'pagsawitan', 'name' => 'Pagsawitan'],
                ['slug' => 'palasan', 'name' => 'Palasan'],
                ['slug' => 'patimbao', 'name' => 'Patimbao'],
                ['slug' => 'san-jose', 'name' => 'San Jose'],
                ['slug' => 'san-juan', 'name' => 'San Juan'],
                ['slug' => 'san-pablo-norte', 'name' => 'San Pablo Norte'],
                ['slug' => 'san-pablo-sur', 'name' => 'San Pablo Sur'],
                ['slug' => 'santisima-cruz', 'name' => 'Santisima Cruz'],
                ['slug' => 'santo-angel-central', 'name' => 'Santo Angel Central'],
                ['slug' => 'santo-angel-norte', 'name' => 'Santo Angel Norte'],
                ['slug' => 'santo-angel-sur', 'name' => 'Santo Angel Sur'],
            ];
            @endphp

            @foreach($barangays as $brgy)
                <button 
                    type="button"
                    class="barangay-btn"
                    data-name="{{ $brgy['name'] }}"
                    data-slug="{{ $brgy['slug'] }}"
                    onclick="selectBarangayAndGo('{{ $brgy['slug'] }}')"
                >
                    {{ $brgy['name'] }}
                </button>
            @endforeach
        </div>

        <div class="no-results" id="noResults" style="display:none;">
            No barangay found. Try a different search term.
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('barangaySearch');
        const barangayGrid = document.getElementById('barangayGrid');
        const noResults = document.getElementById('noResults');

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            const buttons = barangayGrid.querySelectorAll('.barangay-btn');
            let visibleCount = 0;

            buttons.forEach(btn => {
                const name = btn.dataset.name.toLowerCase();
                const match = name.includes(query);
                btn.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        });

        function selectBarangayAndGo(slug) {
            if (window.showLoading) window.showLoading('Redirecting...');
            window.location.href = `/kkprofiling/${slug}`;
        }
    </script>
</body>
</html>
