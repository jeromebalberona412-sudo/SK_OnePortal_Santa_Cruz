{{-- Global Loading Overlay --}}
{{-- Include in every page: @include('dashboard::loading') --}}
<div id="globalLoadingOverlay" role="status" aria-live="polite" aria-label="Loading">
    <div class="gl-content">
        <div class="gl-spinner">
            <div class="gl-spinner-circle"></div>
        </div>
        <p class="gl-message">Loading</p>
        <p class="gl-sub"><span class="gl-dots"></span></p>
    </div>
</div>
