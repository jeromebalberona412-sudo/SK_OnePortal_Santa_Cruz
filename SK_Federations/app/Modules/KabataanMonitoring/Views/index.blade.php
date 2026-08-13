@extends('layout::app')

@section('title', 'Kabataan Monitoring - SK OnePortal')

@push('main-class')
    km-main
@endpush

@push('main-attributes')
    data-detail-base="{{ url('/kabataan-monitoring') }}"
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ url('/modules/kabataan-monitoring/css/kabataan-monitoring.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="km-container">

            <section class="km-page-header">
                <h1 class="km-page-title">Kabataan Monitoring</h1>
            </section>

            <section class="km-filter-bar" aria-label="Kabataan filters">
                <select id="km-year-filter" class="km-select" aria-label="Filter by year">
                    <option value="all">All Years</option>
                </select>
                <select id="km-brgy-filter" class="km-select" aria-label="Filter by barangay">
                    <option value="all">All Barangays</option>
                    <option value="Alipit">Alipit</option>
                    <option value="Bagumbayan">Bagumbayan</option>
                    <option value="Bubukal">Bubukal</option>
                    <option value="Calios">Calios</option>
                    <option value="Duhat">Duhat</option>
                    <option value="Gatid">Gatid</option>
                    <option value="Jasaan">Jasaan</option>
                    <option value="Labuin">Labuin</option>
                    <option value="Malinao">Malinao</option>
                    <option value="Oogong">Oogong</option>
                    <option value="Pagsawitan">Pagsawitan</option>
                    <option value="Palasan">Palasan</option>
                    <option value="Patimbao">Patimbao</option>
                    <option value="Poblacion I">Poblacion I</option>
                    <option value="Poblacion II">Poblacion II</option>
                    <option value="Poblacion III">Poblacion III</option>
                    <option value="Poblacion IV">Poblacion IV</option>
                    <option value="Poblacion V">Poblacion V</option>
                    <option value="San Jose">San Jose</option>
                    <option value="San Juan">San Juan</option>
                    <option value="San Pablo Norte">San Pablo Norte</option>
                    <option value="San Pablo Sur">San Pablo Sur</option>
                    <option value="Santisima Cruz">Santisima Cruz</option>
                    <option value="Santo Angel Central">Santo Angel Central</option>
                    <option value="Santo Angel Norte">Santo Angel Norte</option>
                    <option value="Santo Angel Sur">Santo Angel Sur</option>
                </select>
            </section>

            <div id="km-brgy-cards"></div>
            <p id="km-empty" class="km-empty" hidden>No profiles match your current filters.</p>

        </div>
@endsection

@push('scripts')
<script>
    window.kmConfig = {
        dataUrl: @json(route('api.kabataan-monitoring.index')),
    };
</script>
    <script src="{{ url('/modules/kabataan-monitoring/js/kabataan-monitoring.js') }}?v={{ time() }}"></script>
@endpush
