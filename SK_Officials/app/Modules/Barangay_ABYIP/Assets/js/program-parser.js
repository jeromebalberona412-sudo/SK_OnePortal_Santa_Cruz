function compact(text) {
    return String(text || '').replace(/\s+/g, ' ').trim();
}

export function isProgramHeading(fullLine, cols) {
    const text = compact(fullLine || cols?.ppas);
    if (!text || text.length < 8 || text.length > 90) {
        return false;
    }

    if (cols && (cols.mooe || cols.co || cols.total)) {
        return false;
    }

    if (/RECEIPTS\s+PROGRAM|GENERAL ADMINISTRATION PROGRAM|SK YOUTH DEVELOPMENT AND EMPOWERMENT/i.test(text)) {
        return true;
    }

    return /PROGRAMS?\s*$/i.test(text) && !/^[A-J]\.\s/i.test(text) && !/^I{1,3}\.\s/i.test(text);
}

export function isCategoryHeading(fullLine, cols) {
    const text = compact(fullLine || cols?.ppas);
    if (!text) {
        return false;
    }

    if (cols && (cols.mooe || cols.co || cols.total) && compact(cols.ppas) && compact(cols.ppas).length > 40) {
        return false;
    }

    if (/CURRENT OPERATING EXPENDITURES|Maintenance and Other Operating|Capital Outlay/i.test(text)) {
        return true;
    }

    return /^([A-J])\.\s*\S+/i.test(text) && !(cols && (cols.mooe || cols.co || cols.total) && compact(cols.description));
}

export function looksLikeIncludedBudget(text) {
    return /included\s+in/i.test(String(text || ''));
}

const BULLET_SPLIT = /[\u2022\u25CF\u25E6\uF0B7\uF0D6\uF0A7\uF0B8\u00B7•►▪▫‣⁃]/;

export function splitBulletActivities(ppas) {
    const raw = String(ppas || '');
    const parts = raw
        .split(BULLET_SPLIT)
        .map((part) => compact(part.replace(/^[\-–—]\s*/, '')))
        .filter(Boolean);

    return parts.length > 1 ? parts : [compact(raw)].filter(Boolean);
}

export function startsWithActivityBullet(text) {
    return /^[\s]*[\u2022\u25CF\u25E6\uF0B7\uF0D6\uF0A7\uF0B8\u00B7•►▪▫‣⁃\-–—]/.test(String(text || ''));
}

export function extractYouthLetter(ppas, fullLine) {
    const sources = [compact(fullLine), compact(ppas)].filter(Boolean);

    for (let index = 0; index < sources.length; index += 1) {
        const source = sources[index];
        if (/Receipts\s+Program/i.test(source)) {
            continue;
        }

        const match = source.match(/^([A-J])\.(?:\s+|$|[A-Za-z])/i);
        if (match) {
            return match[1].toUpperCase();
        }
    }

    return '';
}

export function stripYouthLetter(text) {
    return compact(String(text || '').replace(/^[A-J]\.\s*/i, ''));
}

export function isActivityRow(cols, context) {
    const ppas = compact(cols?.ppas);
    const hasBudget = Boolean(cols?.mooe || cols?.co || cols?.total);
    const hasMeta = Boolean(cols?.description || cols?.expected || cols?.performance || cols?.period || cols?.person);

    if (isProgramHeading(cols?.fullLine, cols) || isCategoryHeading(cols?.fullLine, cols)) {
        return false;
    }

    if (context === 'youth' && extractYouthLetter(ppas, cols?.fullLine)) {
        return false;
    }

    return hasBudget || (ppas && hasMeta) || (ppas && context === 'youth' && ppas.length >= 4);
}
