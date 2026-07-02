{{-- Mobile programs drawer — include on Kabataan pages that use kabataan-header --}}
<div id="programsDrawerBackdrop" class="programs-drawer-backdrop" aria-hidden="true"></div>
<aside class="programs-sidebar" id="programsDrawerSidebar" aria-label="Programs menu">
    <div class="sidebar-card">
        <h2 class="sidebar-title">Programs in Your Barangay</h2>
        <p class="sidebar-subtitle">Available programs in Barangay {{ $barangayName ?? 'Your Barangay' }}</p>
        <div class="program-categories" id="programCategoriesDrawerContainer">
            <p class="programs-drawer-loading">Loading programs…</p>
        </div>
    </div>
</aside>
