<section class="gov-card panel barangay-distribution-panel">
    <header class="panel__header">
        <div>
            <h2 class="gov-card__title">Barangay Distribution</h2>
            <p class="gov-card__subtitle">Assignment coverage and gap detection</p>
        </div>
    </header>

    <div class="table-scroll">
        <table class="table-compact" aria-label="Barangay account distribution">
            <thead>
                <tr>
                    <th>Barangay name</th>
                    <th>SKFederation assigned</th>
                    <th>SKOfficials assigned</th>
                    <th>Account count</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="row in barangayDistribution" :key="row.barangay">
                    <tr>
                        <td x-text="row.barangay"></td>
                        <td>
                            <span class="pill" :class="federationBadge(row)" x-text="federationLabel(row)"></span>
                        </td>
                        <td x-text="row.skOfficialsAssigned"></td>
                        <td x-text="row.accountCount"></td>
                        <td>
                            <span
                                class="gap-flag"
                                :class="overallGap(row) ? 'gap-flag--healthy' : 'gap-flag--critical'"
                                x-text="overallGap(row) ? 'Healthy' : 'RED FLAG'"
                            ></span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</section>
