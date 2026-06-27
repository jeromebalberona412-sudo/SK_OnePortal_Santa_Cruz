<div class="schol-schedule-card sports-age-card" style="margin-bottom:20px;">
    <div class="sports-age-card__header">
        <h4 class="schol-schedule-title">Age Classifications</h4>
        <div class="sports-age-card__actions">
            <button type="button" class="schol-btn schol-btn-outline schol-btn-sm" id="sportsUseDefaultAgeBtn">Use Default Age Brackets</button>
            <button type="button" class="schol-btn schol-btn-outline schol-btn-sm" id="sportsOpenAllBtn">Open All</button>
        </div>
    </div>
    <p class="sports-age-card__hint">Set age brackets for each division (ages 15–30 only). Basketball and Volleyball load sport-specific defaults; Other lets you add your own. Toggle which divisions accept applications.</p>

    <div class="sports-age-team-settings">
        <div class="schol-field">
            <label for="sportsMaxTeamMembers">Maximum Team Members</label>
            <input type="number" id="sportsMaxTeamMembers" class="schol-input" value="12" min="1" max="12" step="1">
            <span class="schol-field-hint">Maximum of 12 members per team.</span>
        </div>
    </div>

    <div class="sports-age-table-wrap">
        <table class="sports-age-table" id="sportsAgeClassificationsTable">
            <thead>
                <tr>
                    <th>Classification Name</th>
                    <th>Minimum Age</th>
                    <th>Maximum Age</th>
                    <th>Open</th>
                    <th class="col-actions"></th>
                </tr>
            </thead>
            <tbody id="sportsAgeClassificationsBody"></tbody>
        </table>
    </div>
    <button type="button" class="schol-btn schol-btn-outline schol-btn-sm" id="sportsAddClassificationBtn" style="margin-top:12px;">+ Add Classification</button>
</div>
