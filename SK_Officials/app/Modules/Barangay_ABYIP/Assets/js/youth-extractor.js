import { normalizeCurrency, looksLikeYear } from './validation.js';
import { mergeNumericParts, nearestBudgetColumn } from './budget-parser.js';
import { startsWithActivityBullet } from './program-parser.js';

const LETTER_X_MAX = 110;
const PPAS_X_MAX = 140;
const DESC_X = [140, 218];
const LETTER_HANG = 8;
const EXPECTED_X = [218, 296];
const PERF_X = [296, 360];
const PERIOD_X = [360, 408];
const BUDGET_X_MIN = 400;
const BUDGET_X_MAX = 535;
const PERSON_X_MIN = 535;
const DEFAULT_CENTERS = { mooe: 421, co: 468, total: 504 };
const BUDGET_Y_TOLERANCE = 9;
const LINE_Y_TOLERANCE = 5.5;

function compact(text) {
    return String(text || '').replace(/\s+/g, ' ').trim();
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

function collectPeriod(slice) {
    const text = compact(slice
        .filter((item) => item.x >= PERIOD_X[0] && item.x < PERIOD_X[1])
        .sort((a, b) => b.y - a.y || a.x - b.x)
        .map((item) => item.text)
        .join(' '));

    if (/January/i.test(text) && /December/i.test(text) && /2025/.test(text)) {
        return 'January 01, 2025 to December 31, 2025';
    }

    return text;
}

function collectPerson(slice) {
    const lines = [];
    clusterByY(slice.filter((item) => item.x >= PERSON_X_MIN), LINE_Y_TOLERANCE).forEach((row) => {
        const text = compact(row.parts.map((part) => part.text).join(' '))
            .replace(/Counci(?!l)/gi, 'Council')
            .replace(/\s*\/\s*/g, ' / ');
        if (text && !lines.some((line) => line.toLowerCase() === text.toLowerCase())) {
            lines.push(text);
        }
    });

    return compact(lines.join(' '));
}

function collectAmountTriples(slice, centers) {
    const budgetItems = slice.filter((item) => (
        item.x >= BUDGET_X_MIN
        && item.x < BUDGET_X_MAX
        && !looksLikeYear(item.text)
        && !/^(January|December|to)$/i.test(item.text)
    ));

    const triples = [];
    clusterByY(budgetItems, BUDGET_Y_TOLERANCE).forEach((row) => {
        const assigned = { mooe: '', co: '', total: '' };
        mergeNumericParts(row.parts).forEach((part) => {
            const text = String(part.text || '').trim();
            const column = nearestBudgetColumn(part.x, centers);
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
        } else if (assigned.mooe && !assigned.total) {
            assigned.total = assigned.mooe;
            if (!assigned.co) {
                assigned.co = '0.00';
            }
        } else if (assigned.total && !assigned.mooe && !assigned.co) {
            assigned.mooe = assigned.total;
            assigned.co = '0.00';
        }

        triples.push(assigned);
    });

    return triples;
}

function parseLetterSlice(letter, slice, centers, parentProgram) {
    const leftLines = clusterByY(slice.filter((item) => item.x < PPAS_X_MAX), LINE_Y_TOLERANCE);
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

    const triples = collectAmountTriples(slice, centers);
    const names = activities.length ? activities : (programName ? [programName] : []);
    let description = collectColumn(slice, DESC_X);
    let expected = collectColumn(slice, EXPECTED_X);
    let performance = collectColumn(slice, PERF_X);
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
        period: collectPeriod(slice),
        person: collectPerson(slice),
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

export function reconstructYouthBlocks(items, headerCenters, parentProgram) {
    const ordered = items
        .filter((item) => compact(item.text))
        .sort((a, b) => a.page - b.page || b.y - a.y || a.x - b.x);

    const start = findYouthStart(ordered);
    if (start < 0) {
        return [];
    }

    const centers = headerCenters || DEFAULT_CENTERS;
    const parent = parentProgram || 'SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS';
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
        return parseLetterSlice(entry.letter, slice, centers, parent);
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
