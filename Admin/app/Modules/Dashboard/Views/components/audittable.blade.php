<section class="gov-card panel audit-table-panel">
    <header class="panel__header">
        <div>
            <h2 class="gov-card__title">Recent Audit Activity</h2>
            <p class="gov-card__subtitle">Latest account and security actions captured from the control plane</p>
        </div>

        <button type="button" class="panel-action-btn">View Full Audit Log</button>
    </header>

    <div class="audit-table-wrap">
        <table id="auditActivityTable" class="display compact" aria-label="Recent audit activity">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Account</th>
                    <th>Activity</th>
                    <th>Status</th>
                    <th>IP Address</th>
                    <th>Channel</th>
                    <th>Inspect</th>
                </tr>
            </thead>
            <tbody id="auditActivityBody"></tbody>
        </table>
    </div>
</section>
