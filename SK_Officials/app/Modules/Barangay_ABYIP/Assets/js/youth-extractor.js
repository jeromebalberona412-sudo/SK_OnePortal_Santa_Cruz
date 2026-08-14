import { normalizeCurrency, looksLikeYear } from './validation.js';
import { mergeNumericParts, nearestBudgetColumn } from './budget-parser.js';
import { startsWithActivityBullet } from './program-parser.js';

const LETTER_X_MAX = 110;
const LETTER_HANG = 8;
const BUDGET_Y_TOLERANCE = 9;
const LINE_Y_TOLERANCE = 5.5;

function compact(text) {
    return String(text || '').replace(/\s+/g, ' ').trim();
}

function layoutBounds(layout) {
    const centers = { ...(layout?.centers || {}) };
    const personMin = Number.isFinite(layout?.personMin)
        ? layout.personMin
        : (Number.isFinite(centers.person) ? centers.person : (Number.isFinite(centers.total) ? centers.total + 32 : 535));
    const budgetMin = Number.isFinite(layout?.budgetMin)
        ? layout.budgetMin
        : (Number.isFinite(centers.mooe) ? centers.mooe - 40 : 400);
    const budgetMax = Number.isFinite(layout?.budgetMax)
        ? layout.budgetMax
        : personMin;

    if (!Number.isFinite(centers.mooe)) {
        centers.mooe = budgetMin + ((budgetMax - budgetMin) * 0.22);
    }
    if (!Number.isFinite(centers.total)) {
        centers.total = budgetMax - 10;
    }
    if (!Number.isFinite(centers.co)) {
        centers.co = (centers.mooe + centers.total) / 2;
    }
    if (!Number.isFinite(centers.person)) {
        centers.person = personMin;
    }
    const ppasMax = Number.isFinite(layout?.ppasMax) ? layout.ppasMax : 140;

    return {
        centers: {
            mooe: centers.mooe,
            co: centers.co,
            total: centers.total,
            person: centers.person || personMin,
        },
        ppasMax,
        desc: layout?.desc || [140, 218],
        expected: layout?.expected || [218, 296],
        performance: layout?.performance || [296, 360],
        period: layout?.period || [360, 408],
        budgetMin,
        budgetMax,
        personMin,
    };
}

function letterFromItem(item) {
    if (item.x >= LETTER_X_MAX) {
        return '';
    }

    const match = String(item.text || '').trim().match(/^([A-J])\./i);
    return match ? match[1].toUpperCase() : '';
}

function isLetterMarker(item) {
    return Boolean(letterFromItem(item));
}

function isPageNoise(item) {
    const text = compact(item.text);
    return /ANNUAL BARANGAY YOUTH INVESTMENT PROGRAM|Republic of the Philippines|Province of|Municipality of|Barangay of|^FY\s*20\d{2}$/i.test(text)
        || /^(Code|PPAs|Description|Expected Result|Performance Indicator|Period of Implementation|Budget|Person Responsible|MOOE|CO|Total|Responsible)$/i.test(text);
}

function isYouthEndMarker(item, hasLetters) {
    if (!hasLetters) {
        return false;
    }

    const text = String(item.text || '').trim();
    if (/^Prepared$/i.test(text) && item.x < 220) {
        return true;
    }

    return /^Total$/i.test(text) && item.x < 350;
}

function isBulletGlyph(text) {
    const value = String(text || '').trim();
    return value.length <= 2 && startsWithActivityBullet(value);
}

function clusterByY(items, tolerance) {
    const sorted = items.slice().sort((a, b) => b.y - a.y || a.x - b.x);
    const rows = [];

    sorted.forEach((item) => {
        const last = rows[rows.length - 1];
        if (last && Math.abs(last.y - item.y) <= tolerance) {
            last.parts.push(item);
            last.y = (last.y * (last.parts.length - 1) + item.y) / last.parts.length;
            return;
        }

        rows.push({ y: item.y, parts: [item], page: item.page });
    });

    rows.forEach((row) => {
        row.parts.sort((a, b) => a.x - b.x);
    });

    return rows;
}

function collectColumn(slice, range) {
    return compact(slice
        .filter((item) => item.x >= range[0] && item.x < range[1])
        .sort((a, b) => b.y - a.y || a.x - b.x)
        .map((item) => item.text)
        .join(' '))
        .replace(/\b(January|Jan\.?|December|Dec\.?)\s+\d{1,2},?\s+\d{4}\b/gi, ' ')
        .replace(/\b20\d{2}\b/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function collectPeriod(slice, periodRange) {
    return compact(slice
        .filter((item) => item.x >= periodRange[0] && item.x < periodRange[1])
        .sort((a, b) => b.y - a.y || a.x - b.x)
        .map((item) => item.text)
        .join(' '));
}

function collectPerson(slice, personMin) {
    const lines = [];
    clusterByY(slice.filter((item) => item.x >= personMin && !/^[\d,₱.\-—–]+$/.test(String(item.text || '').trim())), LINE_Y_TOLERANCE).forEach((row) => {
        const text = compact(row.parts.map((part) => part.text).join(' '))
            .replace(/Counci(?!l)/gi, 'Council')
            .replace(/\s*\/\s*/g, ' / ');
        if (text && !/^(Person|Responsible|MOOE|CO|Total)$/i.test(text) && !lines.some((line) => line.toLowerCase() === text.toLowerCase())) {
            lines.push(text);
        }
    });

    return compact(lines.join(' '));
}

function collectAmountTriples(slice, bounds) {
    const budgetItems = slice.filter((item) => (
        item.x >= bounds.budgetMin
        && item.x < bounds.budgetMax
        && !looksLikeYear(item.text)
        && !/^(January|December|to)$/i.test(item.text)
    ));

    const triples = [];
    clusterByY(budgetItems, BUDGET_Y_TOLERANCE).forEach((row) => {
        const assigned = { mooe: '', co: '', total: '' };
        mergeNumericParts(row.parts).forEach((part) => {
            const text = String(part.text || '').trim();
            const column = nearestBudgetColumn(part.x, bounds.centers);
            if (!column) {
                return;
            }

            if (/^[-—–]$/.test(text)) {
                if (!assigned[column]) {
                    assigned[column] = '0.00';
                }
                return;
            }

            const amount = normalizeCurrency(text);
            if (!amount) {
                return;
            }

            if (!assigned[column]) {
                assigned[column] = amount;
            }
        });

        if (!assigned.mooe && !assigned.co && !assigned.total) {
            return;
        }

        if (assigned.mooe && assigned.total && !assigned.co && assigned.mooe === assigned.total) {
            assigned.co = '0.00';
        } else if (assigned.co && assigned.total && !assigned.mooe && assigned.co === assigned.total) {
            assigned.mooe = '0.00';
        }

        triples.push(assigned);
    });

    return triples;
}

function parseLetterSlice(letter, slice, bounds, parentProgram) {
    const leftLines = clusterByY(slice.filter((item) => item.x < bounds.ppasMax), LINE_Y_TOLERANCE);
    let programName = '';
    const activities = [];

    leftLines.forEach((row) => {
        const parts = row.parts;
        const first = String(parts[0]?.text || '').trim();

        if (isLetterMarker(parts[0])) {
            const name = compact(parts.map((part) => part.text).join(' ').replace(/^[A-J]\.\s*/i, ''));
            programName = compact(`${programName} ${name}`);
            return;
        }

        if (isBulletGlyph(first) || startsWithActivityBullet(first)) {
            const name = normalizePpasName(compact(parts
                .slice(isBulletGlyph(first) ? 1 : 0)
                .map((part) => part.text)
                .join(' ')
                .replace(/^[\-–—]\s*/, '')));
            if (name) {
                activities.push(name);
            }
            return;
        }

        const extra = compact(parts.map((part) => part.text).join(' '));
        if (!extra) {
            return;
        }

        if (activities.length) {
            activities[activities.length - 1] = compact(`${activities[activities.length - 1]} ${extra}`);
            return;
        }

        programName = compact(`${programName} ${extra}`);
    });

    const triples = collectAmountTriples(slice, bounds);
    const names = activities.length ? activities : (programName ? [programName] : []);
    let description = collectColumn(slice, bounds.desc);
    let expected = collectColumn(slice, bounds.expected);
    let performance = collectColumn(slice, bounds.performance);
    if (!description && expected && !performance && !looksLikeOutcome(expected)) {
        description = expected;
        expected = '';
    }

    return {
        letter,
        programName,
        parentProgram,
        ppas: names.join(' • '),
        description,
        expected,
        performance,
        period: collectPeriod(slice, bounds.period),
        person: collectPerson(slice, bounds.personMin),
        mooe: triples[0]?.mooe || '',
        co: triples[0]?.co || '',
        total: triples[0]?.total || '',
        mooeList: triples.map((triple) => triple.mooe).filter(Boolean),
        coList: triples.map((triple) => triple.co),
        totalList: triples.map((triple) => triple.total).filter(Boolean),
        page: slice[0]?.page || null,
        sourceText: compact(slice.map((item) => item.text).join(' ')),
    };
}

function findYouthStart(ordered) {
    for (let index = 0; index < ordered.length; index += 1) {
        const item = ordered[index];
        if (item.x > 200) {
            continue;
        }

        if (/SK\s+YOUTH\s+DEVELOPMENT/i.test(item.text)) {
            return index;
        }

        if (!/^SK$/i.test(item.text)) {
            continue;
        }

        const sameLine = ordered.filter((other) => (
            other.page === item.page && Math.abs(other.y - item.y) <= 6
        ));
        const line = compact(sameLine.map((part) => part.text).join(' '));
        if (/YOUTH\s+DEVELOPMENT/i.test(line)) {
            return index;
        }
    }

    return -1;
}

export function reconstructYouthBlocks(items, layout, parentProgram) {
    const ordered = items
        .filter((item) => compact(item.text))
        .sort((a, b) => a.page - b.page || b.y - a.y || a.x - b.x);

    const start = findYouthStart(ordered);
    if (start < 0) {
        return [];
    }

    const bounds = layoutBounds(layout);
    const parent = parentProgram || compact(ordered[start]?.text) || 'SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS';
    const letters = [];
    let endIndex = ordered.length;

    for (let index = start; index < ordered.length; index += 1) {
        const item = ordered[index];
        if (isYouthEndMarker(item, letters.length)) {
            endIndex = index;
            break;
        }
        const letter = letterFromItem(item);
        if (letter && !letters.some((entry) => entry.letter === letter)) {
            letters.push({
                letter,
                index,
                page: item.page,
                y: item.y,
            });
        }
    }

    return letters.map((entry, position) => {
        const next = letters[position + 1] || null;
        const slice = ordered.filter((item, index) => (
            index >= start
            && index < endIndex
            && !isPageNoise(item)
            && itemBelongsToLetter(item, entry, next)
        ));
        return parseLetterSlice(entry.letter, slice, bounds, parent);
    }).filter((block) => block.letter && (block.ppas || block.programName));
}

function itemBelongsToLetter(item, entry, next) {
    const otherLetter = letterFromItem(item);
    if (otherLetter && otherLetter !== entry.letter) {
        return false;
    }

    if (item.page < entry.page) {
        return false;
    }

    if (item.page === entry.page && item.y > entry.y + LETTER_HANG) {
        return false;
    }

    if (!next) {
        return true;
    }

    if (item.page > next.page) {
        return false;
    }

    return !(item.page === next.page && item.y <= next.y + LETTER_HANG);
}

function normalizePpasName(name) {
    return compact(String(name || '').replace(/([a-z])([A-Z])/g, '$1 $2'));
}

function looksLikeOutcome(text) {
    return /^(Increased|Decreased|Improved|Improve|Percentage|Number of|Active participation|Healthier)\b/i.test(compact(text));
}
