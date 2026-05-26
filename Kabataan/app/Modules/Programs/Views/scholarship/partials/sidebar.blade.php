@php
    $schNavItems = [
        ['id' => 'personal', 'label' => 'Personal Information', 'icon' => 'user'],
        ['id' => 'educational', 'label' => 'Educational Background', 'icon' => 'book'],
        ['id' => 'background', 'label' => 'Background Information', 'icon' => 'family'],
        ['id' => 'additional', 'label' => 'Additional Information', 'icon' => 'info'],
        ['id' => 'requirements', 'label' => 'Requirements', 'icon' => 'doc'],
    ];
@endphp

<aside class="sk-side" id="skSideNav">
    <div class="sk-side__panel">
        <div class="sk-side__head">
            <div class="sk-side__avatar" id="schProfilePhoto">
                <img src="https://ui-avatars.com/api/?name=Applicant&background=0450a8&color=fff&size=128" alt="Applicant" id="schProfileImg">
                <span class="sk-side__avatar-ring"></span>
            </div>
            <div class="sk-side__head-text">
                <p class="sk-side__label">SK Scholarship</p>
                <h2 class="sk-side__title">Application Steps</h2>
            </div>
            <button type="button" class="sk-side__collapse" id="skSideCollapse" aria-label="Collapse navigation" aria-expanded="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
        </div>

        <button type="button" class="sk-side__photo-btn" id="schEditPhotoBtn">
            <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
            Edit Photo
        </button>
        <input type="file" id="schPhotoInput" accept="image/*" hidden>

        <nav class="sk-side__nav" aria-label="Application sections">
            <p class="sk-side__group">Your progress</p>
            @foreach ($schNavItems as $index => $item)
            <button type="button" class="sk-side__link {{ $index === 0 ? 'is-active' : '' }}" data-section="{{ $item['id'] }}">
                <span class="sk-side__link-icon" aria-hidden="true">
                    @if ($item['icon'] === 'user')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    @elseif ($item['icon'] === 'book')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                    @elseif ($item['icon'] === 'family')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    @elseif ($item['icon'] === 'info')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    @else
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                    @endif
                </span>
                <span class="sk-side__link-body">
                    <span class="sk-side__step">Step {{ $index + 1 }}</span>
                    <span class="sk-side__name">{{ $item['label'] }}</span>
                </span>
                <span class="sk-side__chevron" aria-hidden="true">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </span>
            </button>
            @endforeach
        </nav>

        <div class="sk-side__footer">
            <a href="{{ route('dashboard') }}" class="sk-side__back">
                <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                Back to Dashboard
            </a>
        </div>
    </div>
</aside>
<button type="button" class="sk-side__mobile-toggle" id="skSideMobileToggle" aria-label="Open application menu">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    Menu
</button>

