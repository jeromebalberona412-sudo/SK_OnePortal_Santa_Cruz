@extends('program_accomplishments::layout')

@section('title', 'Barangay Accomplishments — SK OnePortal Kabataan')

@section('styles')
    @parent
    @vite([
        'app/Modules/Program_Accomplishments/assets/css/barangay-accomplishments.css',
    ])
@endsection

@section('scripts')
    @parent
    @vite([
        'app/Modules/Program_Accomplishments/assets/js/barangay-accomplishments.js',
    ])
@endsection

@section('content')
<div class="barangay-accomplishments-page kabataan-page-section">
    <section class="accomplishments-hero">
        <div class="container accomplishments-shell">
            <span class="accomplishments-eyebrow">Transparency</span>
            <h1>Barangay Accomplishments</h1>
            <p class="accomplishments-hero-text">
                Browse Annual Barangay Accomplishments across all barangays in Santa Cruz, Laguna.
            </p>

            <label class="accomplishments-search" for="barangayAccomplishmentsSearch">
                <span aria-hidden="true">🔍</span>
                <input
                    type="search"
                    id="barangayAccomplishmentsSearch"
                    placeholder="Search barangays..."
                    autocomplete="off"
                >
            </label>
        </div>
    </section>

    <section class="accomplishments-list-section">
        <div class="container accomplishments-shell">
            @if ($barangays->isEmpty())
                <div class="accomplishments-empty-state">
                    <h2>No barangays available yet</h2>
                    <p>Barangay listings will appear here once they are added to the system.</p>
                </div>
            @else
                <div class="accomplishments-summary">
                    <span id="accomplishmentsResultCount">Showing {{ $barangays->count() }} barangays</span>
                </div>

                <div class="kabataan-barangay-grid accomplishments-barangay-grid" id="accomplishmentsBarangayGrid">
                    @foreach ($barangays as $barangay)
                        <a
                            href="{{ route('program_accomplishments.barangays.show', $barangay->slug) }}"
                            class="kabataan-barangay-card accomplishments-barangay-card"
                            data-barangay-name="{{ strtolower($barangay->name) }}"
                        >
                            <div class="accomplishments-card-top">
                                <span class="kabataan-barangay-name">{{ $barangay->name }}</span>
                                <span class="accomplishments-status-badge {{ $barangay->accomplishments_exists ? 'is-available' : 'is-missing' }}">
                                    {{ $barangay->accomplishments_exists ? 'Accomplishments available' : 'No Accomplishments uploaded' }}
                                </span>
                            </div>
                            <p class="accomplishments-card-copy">View budget summary and PPA line items for this barangay.</p>
                            <span class="accomplishments-card-link">View Accomplishments →</span>
                        </a>
                    @endforeach
                </div>

                <div class="accomplishments-empty-state accomplishments-filter-empty" id="accomplishmentsFilterEmpty" hidden>
                    <h2>No matching barangays</h2>
                    <p>Try a different search term.</p>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
