<div class="show-archive-wrap">
    <label for="skArchiveSelect" class="show-archive-label">Show Archive:</label>
    <select
        id="skArchiveSelect"
        class="show-archive-select"
        aria-label="Show archive by SK term"
        data-terms='@json($archiveTerms ?? [])'
        data-active-term="{{ $activeArchiveTermId ?? '' }}"
        data-terms-url="{{ route('api.archive.terms') }}"
    ></select>
</div>
