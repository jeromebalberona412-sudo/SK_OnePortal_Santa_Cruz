/**
 * Sport-specific default age classifications.
 * Source of truth: app/Modules/Program_Management/config/sports-age-classifications.php
 * (also injected as window.SPORTS_AGE_CLASSIFICATIONS on the schedule page).
 */
const SPORTS_AGE_CLASSIFICATIONS_FALLBACK = {
    basketball: [
        { id: 'cls_mosquito_division', name: 'Mosquito Division', min_age: 15, max_age: 17 },
        { id: 'cls_midget_division', name: 'Midget Division', min_age: 18, max_age: 21 },
        { id: 'cls_junior_division', name: 'Junior Division', min_age: 22, max_age: 25 },
        { id: 'cls_senior_division', name: 'Senior Division', min_age: 26, max_age: 30 },
    ],
    volleyball: [
        { id: 'cls_youth_division', name: 'Youth Division', min_age: 15, max_age: 17 },
        { id: 'cls_cadet_division', name: 'Cadet Division', min_age: 18, max_age: 20 },
        { id: 'cls_intermediate_division', name: 'Intermediate Division', min_age: 21, max_age: 23 },
        { id: 'cls_open_division', name: 'Open Division', min_age: 24, max_age: 26 },
        { id: 'cls_senior_kk_division', name: 'Senior KK Division', min_age: 27, max_age: 30 },
    ],
    other: [],
};

function getSportsAgeClassificationConfig() {
    const injected = window.SPORTS_AGE_CLASSIFICATIONS;
    if (injected && typeof injected === 'object') {
        return injected;
    }

    return SPORTS_AGE_CLASSIFICATIONS_FALLBACK;
}

function normalizeSportKey(sportKey) {
    const key = String(sportKey || '').trim().toLowerCase();
    if (key === 'basketball' || key === 'volleyball' || key === 'other') {
        return key;
    }

    return 'basketball';
}

function getDefaultAgeClassificationsForSport(sportKey) {
    const config = getSportsAgeClassificationConfig();
    const key = normalizeSportKey(sportKey);
    const entries = Array.isArray(config[key])
        ? config[key]
        : (key === 'other' ? [] : (config.basketball || []));

    return entries.map((item) => ({
        id: item.id || `cls_${String(item.name || 'division').toLowerCase().replace(/[^a-z0-9]+/g, '_')}`,
        name: item.name,
        min_age: item.min_age,
        max_age: item.max_age,
        is_open: item.is_open !== false,
    }));
}

function getSelectedSportKey() {
    return document.getElementById('sportsDisciplineKey')?.value?.trim() || '';
}

window.SportsAgeClassifications = {
    getSportsAgeClassificationConfig,
    getDefaultAgeClassificationsForSport,
    getSelectedSportKey,
    normalizeSportKey,
};
