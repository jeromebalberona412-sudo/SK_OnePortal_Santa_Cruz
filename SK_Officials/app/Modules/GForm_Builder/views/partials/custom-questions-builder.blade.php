@php
    $sectionTitle = $sectionTitle ?? 'Custom Questions';
    $hint = $hint ?? 'Add custom questions that Kabataan members will answer when applying.';
    $emptyMessage = $emptyMessage ?? 'No questions yet. Click <strong>Add Question</strong> to start building your custom form.';
@endphp
<div class="spfb-section-card spfb-section-builder gform-questions-builder">
    <div class="spfb-section-label">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        {{ $sectionTitle }}
        <span class="spfb-badge" id="spfbQuestionCount">0 questions</span>
    </div>
    <p class="spfb-builder-hint">{{ $hint }}</p>

    <div id="spfbQuestionList" class="spfb-question-list">
        <div class="spfb-empty-state" id="spfbEmptyState">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <p>{!! $emptyMessage !!}</p>
        </div>
    </div>

    <button type="button" class="spfb-add-question-btn" id="spfbAddQuestionBtn">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Question
    </button>
</div>
