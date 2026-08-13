const DASH_VALUES = new Set(['', '-', '—', '–', 'n/a', 'na', 'none']);

export function looksLikeYear(value) {
    const digits = String(value ?? '').replace(/[^0-9]/g, '');
    return /^(19|20)\d{2}$/.test(digits);
}

export function normalizeCurrency(value) {
    if (value === null || value === undefined) {
        return null;
    }

    const raw = String(value).trim();
    if (DASH_VALUES.has(raw.toLowerCase())) {
        return null;
    }

    const pesoMatch = raw.match(/₱?\s*([\d,]+(?:\.\d{1,2})?)/);
    const source = pesoMatch ? pesoMatch[1] : raw;

    if (looksLikeYear(source) && !/[.,]/.test(source)) {
        return null;
    }

    const decimalMatch = source.match(/^([\d,]+)\.(\d{1,2})$/);
    if (decimalMatch) {
        const whole = decimalMatch[1].replace(/,/g, '');
        if (looksLikeYear(whole)) {
            return null;
        }
        const cents = decimalMatch[2].padEnd(2, '0');
        return `${whole}.${cents}`;
    }

    // PDF/OCR sometimes writes cents with a comma: "20,000,00"
    const commaDecimal = source.match(/^(\d{1,3}(?:,\d{3})+),(\d{2})$/);
    if (commaDecimal) {
        const whole = commaDecimal[1].replace(/,/g, '');
        if (looksLikeYear(whole)) {
            return null;
        }
        return `${whole}.${commaDecimal[2]}`;
    }

    const grouped = source.match(/^(\d{1,3}(?:,\d{3})+)$/);
    if (grouped) {
        return `${grouped[1].replace(/,/g, '')}.00`;
    }

    const digitsOnly = source.replace(/[^0-9.\-]/g, '');
    if (!digitsOnly || digitsOnly === '.' || digitsOnly === '-') {
        return null;
    }

    if (looksLikeYear(digitsOnly)) {
        return null;
    }

    if (/^-?\d+$/.test(digitsOnly)) {
        return `${digitsOnly}.00`;
    }

    if (/^-?\d+\.\d+$/.test(digitsOnly)) {
        const [whole, fraction] = digitsOnly.split('.');
        return `${whole}.${fraction.slice(0, 2).padEnd(2, '0')}`;
    }

    return null;
}

export function parseCurrencyNumber(value) {
    const normalized = normalizeCurrency(value);
    if (normalized === null) {
        return null;
    }

    const amount = Number.parseFloat(normalized);
    return Number.isFinite(amount) ? amount : null;
}

export function validateBudgetTriple(mooe, co, total) {
    const mooeValue = parseCurrencyNumber(mooe) ?? 0;
    const coValue = parseCurrencyNumber(co) ?? 0;
    const totalParsed = parseCurrencyNumber(total);
    const hasAny = parseCurrencyNumber(mooe) !== null
        || parseCurrencyNumber(co) !== null
        || totalParsed !== null;

    if (!hasAny) {
        return {
            status: 'warning',
            message: 'Budget values were not found in the source PDF.',
            manualReviewRequired: true,
        };
    }

    if (totalParsed === null) {
        return {
            status: 'warning',
            message: 'Total is missing. Source MOOE/CO values were not changed.',
            manualReviewRequired: true,
        };
    }

    const sum = Math.round((mooeValue + coValue) * 100) / 100;
    if (Math.abs(sum - totalParsed) > 0.01) {
        return {
            status: 'warning',
            message: `MOOE (${mooeValue.toFixed(2)}) + CO (${coValue.toFixed(2)}) = ${sum.toFixed(2)}, but Total is ${totalParsed.toFixed(2)}.`,
            manualReviewRequired: true,
        };
    }

    return {
        status: 'valid',
        message: '',
        manualReviewRequired: false,
    };
}

export function formatPeso(value) {
    const amount = parseCurrencyNumber(value);
    if (amount === null) {
        return '—';
    }

    return new Intl.NumberFormat('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}
