@extends('homepage::layout')

@section('title', 'Barangay ABYIP — SK OnePortal Kabataan')

@section('content')
<div class="barangay-abyip-page kabataan-page-section">
    <section class="abyip-hero">
        <div class="container abyip-shell">
            <span class="abyip-eyebrow">Transparency</span>
            <h1>Barangay ABYIP</h1>
            <p class="abyip-hero-text">
                Browse Annual Barangay Youth Investment Programs across all barangays in Santa Cruz, Laguna.
            </p>

            <label class="abyip-search" for="barangayAbyipSearch">
                <span aria-hidden="true">🔍</span>
                <input
                    type="search"
                    id="barangayAbyipSearch"
                    placeholder="Search barangays..."
                    autocomplete="off"
                >
            </label>
        </div>
    </section>

    <section class="abyip-list-section">
        <div class="container abyip-shell">
            @if ($barangays->isEmpty())
                <div class="abyip-empty-state">
                    <h2>No barangays available yet</h2>
                    <p>Barangay listings will appear here once they are added to the system.</p>
                </div>
            @else
                <div class="abyip-summary">
                    <span id="abyipResultCount">Showing {{ $barangays->count() }} barangays</span>
                </div>

                <div class="kabataan-barangay-grid abyip-barangay-grid" id="abyipBarangayGrid">
                    @foreach ($barangays as $barangay)
                        <a
                            href="{{ route('homepage.barangays.show', $barangay->slug) }}"
                            class="kabataan-barangay-card abyip-barangay-card"
                            data-barangay-name="{{ strtolower($barangay->name) }}"
                        >
                            <div class="abyip-card-top">
                                <span class="kabataan-barangay-name">{{ $barangay->name }}</span>
                                <span class="abyip-status-badge {{ $barangay->abyips_exists ? 'is-available' : 'is-missing' }}">
                                    {{ $barangay->abyips_exists ? 'ABYIP available' : 'No ABYIP uploaded' }}
                                </span>
                            </div>
                            <p class="abyip-card-copy">View budget summary and PPA line items for this barangay.</p>
                            <span class="abyip-card-link">View ABYIP →</span>
                        </a>
                    @endforeach
                </div>

                <div class="abyip-empty-state abyip-filter-empty" id="abyipFilterEmpty" hidden>
                    <h2>No matching barangays</h2>
                    <p>Try a different search term.</p>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
