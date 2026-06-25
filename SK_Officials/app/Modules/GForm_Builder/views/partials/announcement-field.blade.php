@php
    $required = $required ?? false;
    $hint = $hint ?? null;
@endphp
<div class="spfb-announcement-section gform-announcement-field">
    <label class="spfb-announcement-label" for="spfbAnnouncement">
        Announcement
        @if($required)
            <span style="color:#ef4444;"> *</span>
        @endif
    </label>
    @if($hint)
    <p class="spfb-announcement-hint">{{ $hint }}</p>
    @endif
    <textarea
        id="spfbAnnouncement"
        class="spfb-announcement-textarea"
        maxlength="500"
        placeholder="Enter announcement or instructions for applicants..."
        rows="3"></textarea>
    <div class="spfb-announcement-counter"><span id="spfbAnnouncementCount">0</span>/500</div>
</div>
