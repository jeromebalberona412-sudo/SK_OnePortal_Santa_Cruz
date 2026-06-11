<script>window.__BARANGAY_DISTRIBUTION__ = @json($barangayDistribution ?? []);</script>

<section class="gov-card panel barangay-distribution-panel" id="barangayDistributionPanel">
    <header class="panel__header">
        <div>
            <h2 class="gov-card__title">Barangay Distribution</h2>
            <p class="gov-card__subtitle">Federation and official account spread across Santa Cruz barangays</p>
        </div>

        <button type="button" class="panel-action-btn panel-action-btn--export" onclick="exportBarangayDistribution()">Export</button>
    </header>

    <div class="barangay-table-wrap">
        <table class="table-compact barangay-distribution-table" aria-label="Barangay account distribution">
            <thead>
                <tr>
                    <th>Barangay</th>
                    <th>Federation Assigned</th>
                    <th>Official Accounts</th>
                    <th>Total Accounts</th>
                </tr>
            </thead>
            <tbody id="barangayDistributionBody"></tbody>
        </table>
    </div>
</section>
