import { normalizeCurrency, validateBudgetTriple } from './validation.js';

const DASH_PATTERN = /^[-—–]$/;
const NUMERIC_X_GAP = 14;

export function mergeNumericParts(parts) {
    const sorted = parts.slice().sort((a, b) => a.x - b.x);
    const merged = [];
    let buffer = '';
    let bufferX = 0;
    let bufferEnd = 0;

    sorted.forEach((part) => {
        const text = String(part.text || '').replace(/\s/g, '');
        if (!text) {
            return;
        }

        const isNumericFragment = /^[\d,.₱\-—–]+$/.test(text);
        if (isNumericFragment) {
            const gap = part.x - bufferEnd;
            if (buffer && gap <= NUMERIC_X_GAP) {
                buffer += text;
                bufferEnd = Math.max(bufferEnd, part.x + (part.width || text.length * 4));
                return;
            }

            if (buffer) {
                merged.push({ x: bufferX, text: buffer, width: bufferEnd - bufferX });
            }

            buffer = text;
            bufferX = part.x;
            bufferEnd = part.x + (part.width || text.length * 4);
            return;
        }

        if (buffer) {
            merged.push({ x: bufferX, text: buffer, width: bufferEnd - bufferX });
            buffer = '';
        }

        merged.push({ x: part.x, text, width: part.width || 0 });
    });

    if (buffer) {
        merged.push({ x: bufferX, text: buffer, width: bufferEnd - bufferX });
    }

    return merged;
}

export function detectBudgetHeaderCenters(pageRows) {
    let pending = {};
    let width = null;

    for (let i = 0; i < pageRows.length; i += 1) {
        const entry = pageRows[i];
        width = entry.width;
        const found = {};

        entry.row.parts.forEach((part) => {
            const text = String(part.text || '').trim().toLowerCase();
            const x = part.x + ((part.width || 0) / 2);

            if (text === 'mooe') {
                found.mooe = x;
            } else if (text === 'co') {
                found.co = x;
            } else if (text === 'total') {
                found.total = x;
            }
        });

        if (found.mooe !== undefined || found.co !== undefined || found.total !== undefined) {
            pending = { ...pending, ...found };
        } else if (pending.mooe === undefined || pending.total === undefined) {
            pending = {};
        }

        if (pending.mooe !== undefined && pending.total !== undefined) {
            if (pending.co === undefined) {
                pending.co = (pending.mooe + pending.total) / 2;
            }

            return { width, centers: pending };
        }
    }

    return null;
}

export function isInBudgetRegion(x, centers) {
    const xs = [centers?.mooe, centers?.co, centers?.total].filter((value) => Number.isFinite(value));
    if (!xs.length) {
        return false;
    }

    return x >= Math.min(...xs) - 45 && x <= Math.max(...xs) + 55;
}

export function isBudgetToken(text, x, centers) {
    const value = String(text || '').trim();
    if (!value || !isInBudgetRegion(x, centers)) {
        return false;
    }

    if (DASH_PATTERN.test(value)) {
        return true;
    }

    return /[\d,]+\.\d{2}/.test(value)
        || /^\d{1,3}(?:,\d{3})+$/.test(value)
        || /^\d{4,}$/.test(value.replace(/,/g, ''));
}

export function nearestBudgetColumn(x, centers) {
    const candidates = [
        { column: 'mooe', x: centers.mooe },
        { column: 'co', x: centers.co },
        { column: 'total', x: centers.total },
    ].filter((candidate) => Number.isFinite(candidate.x));

    if (!candidates.length) {
        return null;
    }

    let best = candidates[0];
    let bestDistance = Math.abs(x - best.x);

    candidates.slice(1).forEach((candidate) => {
        const distance = Math.abs(x - candidate.x);
        if (distance < bestDistance) {
            best = candidate;
            bestDistance = distance;
        }
    });

    return best.column;
}

export function assignBudgetByPosition(parts, headerCenters, budgetColumn) {
    const assigned = { mooe: null, co: null, total: null };

    if (!headerCenters) {
        return assigned;
    }

    mergeNumericParts(parts).forEach((part) => {
        const text = String(part.text || '').trim();
        if (!isBudgetToken(text, part.x, headerCenters)) {
            return;
        }

        const column = nearestBudgetColumn(part.x, headerCenters);
        if (!column) {
            return;
        }

        if (DASH_PATTERN.test(text)) {
            if (assigned[column] === null) {
                assigned[column] = '0.00';
            }
            return;
        }

        const amount = normalizeCurrency(text);
        if (!amount) {
            return;
        }

        if (assigned[column] === null) {
            assigned[column] = amount;
        }
    });

    if (assigned.mooe && assigned.total && assigned.co === null) {
        if (Math.abs(Number.parseFloat(assigned.mooe) - Number.parseFloat(assigned.total)) < 0.01) {
            assigned.co = '0.00';
        }
    } else if (assigned.co && assigned.total && assigned.mooe === null) {
        if (Math.abs(Number.parseFloat(assigned.co) - Number.parseFloat(assigned.total)) < 0.01) {
            assigned.mooe = '0.00';
        }
    } else if (budgetColumn === 'co' && assigned.total && !assigned.co && !assigned.mooe) {
        assigned.co = assigned.total;
        assigned.mooe = '0.00';
    } else if (budgetColumn === 'mooe' && assigned.total && !assigned.mooe && !assigned.co) {
        assigned.mooe = assigned.total;
        assigned.co = '0.00';
    }

    const validation = validateBudgetTriple(assigned.mooe, assigned.co, assigned.total);
    assigned.validation = validation;

    return assigned;
}
