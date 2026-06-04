@php
    $required = $required ?? true;
    $hint = $hint ?? 'This message will be shown to Kabataan members when they open the application form.';
@endphp
<div class="spfb-announcement-section gform-announcement-field">
    <label class="spfb-announcement-label" for="spfbAnnouncement">
        Announcement @if($required)<span style="color:#ef4444;">*</span>@endif
    </label>
    <p class="spfb-announcement-hint">{{ $hint }}</p>
    <textarea
        id="spfbAnnouncement"
        class="spfb-announcement-textarea"
        maxlength="500"
        placeholder="Enter announcement or instructions for applicants..."
        rows="3"></textarea>
    <div class="spfb-announcement-counter"><span id="spfbAnnouncementCount">0</span>/500</div>
</div>
