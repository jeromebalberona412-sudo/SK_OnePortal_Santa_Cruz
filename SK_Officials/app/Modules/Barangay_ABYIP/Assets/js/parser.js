import { normalizeCurrency, validateBudgetTriple } from './validation.js';
import { assignBudgetByPosition, detectBudgetHeaderCenters, mergeNumericParts } from './budget-parser.js';
import {
    extractYouthLetter as detectYouthLetter,
    isActivityRow,
    isProgramHeading,
    looksLikeIncludedBudget,
    splitBulletActivities,
    startsWithActivityBullet,
    stripYouthLetter as stripDetectedYouthLetter,
} from './program-parser.js';
import { reconstructYouthBlocks } from './youth-extractor.js';

const COLUMN_DEFAULTS = {
    ppas: [0, 0.20],
    description: [0.20, 0.34],
    expected: [0.34, 0.42],
    performance: [0.42, 0.50],
    period: [0.50, 0.62],
    mooe: [0.62, 0.70],
    co: [0.70, 0.74],
    total: [0.74, 0.82],
    person: [0.82, 1.05],
};

const Y_TOLERANCE = 4.5;

function midpoint(left, right) {
    return (left + right) / 2;
}

function compact(text) {
    return String(text || '').replace(/\s+/g, ' ').trim();
}

function isRepeatedHeaderLine(line, pageNum) {
    const text = compact(line);
    if (!text) {
        return true;
    }

    if (/^(Code|PPAs|Description|Expected Result|Performance Indicator|Period of Implementation|Budget|Person Responsible|MOOE|CO|Total|Responsible)$/i.test(text)) {
        return true;
    }

    if (/^MOOE\s+CO\s+Total$/i.test(text)) {
        return true;
    }

    if (pageNum > 1 && /ANNUAL BARANGAY YOUTH INVESTMENT PROGRAM/i.test(text)) {
        return true;
    }

    if (pageNum > 1 && /^(Republic of the Philippines|Region\s|Province of|Municipality of|BARANGAY\s|SANGGUNIANG KABATAAN)/i.test(text)) {
        return true;
    }

    if (/^Page\s+\d+/i.test(text) || /^\d+\s*\/\s*\d+$/.test(text)) {
        return true;
    }

    return false;
}

function isSignatureLine(line) {
    return /Prepared\s+by|Approved\s+by|Reviewed\s+by/i.test(line);
}

function startsWithBullet(text) {
    return startsWithActivityBullet(text);
}

function extractYouthLetter(ppas, fullLine) {
    return detectYouthLetter(ppas, fullLine);
}

function stripYouthLetter(text) {
    return stripDetectedYouthLetter(text);
}

function clusterItemsIntoRows(items) {
    const sorted = items.slice().sort((a, b) => b.y - a.y || a.x - b.x);
    const rows = [];

    sorted.forEach((item) => {
        const last = rows[rows.length - 1];
        if (last && Math.abs(last.y - item.y) <= Y_TOLERANCE) {
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

function collectColumnText(parts, width, startRatio, endRatio) {
    const start = width * startRatio;
    const end = width * endRatio;

    return parts
        .filter((part) => part.x >= start && part.x < end)
        .map((part) => part.text)
        .join(' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function extractColumnAmounts(parts, width, startRatio, endRatio) {
    const start = width * startRatio;
    const end = width * endRatio;
    const columnParts = parts.filter((part) => part.x >= start && part.x < end);

    return mergeNumericParts(columnParts)
        .map((part) => normalizeCurrency(part.text))
        .filter(Boolean);
}

function normalizePersonLabel(value) {
    return compact(value)
        .replace(/Counci(?!l)/i, 'Council')
        .replace(/(Sangguniang)(Kabataan)/i, '$1 $2')
        .replace(/(Kabataan)(Council)/i, '$1 $2')
        .replace(/\b(SK)(Chairman|Treasurer|Chairperson)/i, '$1 $2')
        .replace(/\s*\/\s*/g, ' / ');
}

function extractPersonFromFullLine(fullLine) {
    const source = compact(fullLine);
    const patterns = [
        /Sangguniang\s*Kabataan\s*Counci[l]?\s*\/\s*BADAC/i,
        /Sangguniang\s*Kabataan\s*Counci[l]?\s*\/\s*ALS/i,
        /SK\s*Chairman\s*\/\s*SK\s*Treasurer/i,
        /Sangguniang\s*Kabataan\s*Counci[l]?/i,
        /SK\s*Chairperson/i,
        /SK\s*Chairman/i,
        /SK\s*Treasurer/i,
    ];

    for (let i = 0; i < patterns.length; i += 1) {
        const match = source.match(patterns[i]);
        if (match) {
            return normalizePersonLabel(match[0]);
        }
    }

    return '';
}

function extractPersonResponsibleValue(parts, width, startRatio) {
    const threshold = width * (startRatio !== undefined ? startRatio : 0.82);
    const raw = parts
        .filter((part) => part.x >= threshold && !/^[\d,₱.\-—–]+$/.test(String(part.text || '').trim()))
        .map((part) => part.text)
        .join(' ');

    return extractPersonFromFullLine(raw) || extractPersonFromFullLine(parts.map((part) => part.text).join(' '));
}

function detectColumnBounds(pageRows) {
    for (let i = 0; i < pageRows.length; i += 1) {
        const { width, row } = pageRows[i];
        const markers = {};

        row.parts.forEach((part) => {
            const ratio = part.x / width;
            const text = compact(part.text).toLowerCase();

            if (text === 'mooe') {
                markers.mooe = ratio;
            } else if (text === 'co') {
                markers.co = ratio;
            } else if (text === 'total') {
                markers.total = ratio;
            } else if (text === 'description') {
                markers.description = ratio;
            } else if (text.indexOf('expected') !== -1) {
                markers.expected = markers.expected === undefined ? ratio : Math.min(markers.expected, ratio);
            } else if (/^performance$/i.test(text) || text.indexOf('indicator') !== -1) {
                markers.performance = markers.performance === undefined ? ratio : Math.min(markers.performance, ratio);
            } else if (text.indexOf('period') !== -1 || text.indexOf('implementation') !== -1) {
                markers.period = markers.period === undefined ? ratio : Math.min(markers.period, ratio);
            } else if (text.indexOf('person') !== -1 || text.indexOf('responsible') !== -1) {
                markers.person = markers.person === undefined ? ratio : Math.min(markers.person, ratio);
            } else if (/^ppas$/i.test(text) || text.indexOf('programs, projects') !== -1) {
                markers.ppas = markers.ppas === undefined ? ratio : Math.min(markers.ppas, ratio);
            }
        });

        if (markers.mooe === undefined || markers.total === undefined) {
            continue;
        }

        const co = markers.co !== undefined ? markers.co : markers.mooe + 0.04;
        const person = markers.person !== undefined ? markers.person : 0.84;
        const performance = markers.performance !== undefined ? markers.performance : 0.44;
        const period = markers.period !== undefined ? markers.period : 0.54;
        const expected = markers.expected !== undefined ? markers.expected : 0.38;
        const description = markers.description !== undefined ? markers.description : 0.22;
        const ppas = markers.ppas !== undefined ? markers.ppas : 0.05;

        return {
            ppas: [0, midpoint(ppas, description)],
            description: [midpoint(ppas, description), midpoint(description, expected)],
            expected: [midpoint(description, expected), midpoint(expected, performance)],
            performance: [midpoint(expected, performance), midpoint(performance, period)],
            period: [midpoint(performance, period), midpoint(period, markers.mooe)],
            mooe: [midpoint(period, markers.mooe), midpoint(markers.mooe, co)],
            co: [midpoint(markers.mooe, co), midpoint(co, markers.total)],
            total: [midpoint(co, markers.total), midpoint(markers.total, person)],
            person: [midpoint(markers.total, person), 1.05],
        };
    }

    return COLUMN_DEFAULTS;
}

function parseRowColumns(row, width, bounds, budgetColumn, headerCenters) {
    const columns = bounds || COLUMN_DEFAULTS;
    const positioned = assignBudgetByPosition(row.parts, headerCenters, budgetColumn);
    const hasPositionedBudget = Boolean(positioned.mooe || positioned.co || positioned.total);
    const mooeAmounts = positioned.mooe
        ? [positioned.mooe]
        : (hasPositionedBudget ? [] : extractColumnAmounts(row.parts, width, columns.mooe[0], columns.mooe[1]));
    const coAmounts = positioned.co
        ? [positioned.co]
        : (hasPositionedBudget ? [] : extractColumnAmounts(row.parts, width, columns.co[0], columns.co[1]));
    const totalAmounts = positioned.total
        ? [positioned.total]
        : (hasPositionedBudget ? [] : extractColumnAmounts(row.parts, width, columns.total[0], columns.total[1]));

    const fullLine = compact(row.parts.map((part) => part.text).join(' '));

    return {
        ppas: collectColumnText(row.parts, width, columns.ppas[0], columns.ppas[1]),
        description: collectColumnText(row.parts, width, columns.description[0], columns.description[1]),
        expected: collectColumnText(row.parts, width, columns.expected[0], columns.expected[1]),
        performance: collectColumnText(row.parts, width, columns.performance[0], columns.performance[1]),
        period: collectColumnText(row.parts, width, columns.period[0], columns.period[1]),
        mooe: mooeAmounts[0] || '',
        co: coAmounts[0] || '',
        total: totalAmounts[0] || '',
        mooeAmounts,
        coAmounts,
        totalAmounts,
        person: extractPersonResponsibleValue(row.parts, width, columns.person[0]),
        fullLine,
        page: row.page || 1,
    };
}

function hasStructuredTableData(cols) {
    const hasMoney = Boolean(cols.mooe || cols.co || cols.total);
    const hasMeta = Boolean(cols.description || cols.expected || cols.performance || cols.period || cols.person);
    const hasPpas = Boolean(cols.ppas);

    return hasMoney || (hasPpas && hasMeta);
}

function appendField(block, key, value) {
    const text = compact(value);
    if (!text) {
        return;
    }

    if (!block[key]) {
        block[key] = text;
        return;
    }

    if (!block[key].includes(text)) {
        block[key] = compact(`${block[key]} ${text}`);
    }
}

function pushAmount(list, value) {
    const amount = compact(value);
    if (!amount) {
        return;
    }
    list.push(amount);
}

function mergeRowIntoBlock(block, cols) {
    appendYouthPpas(block, cols.ppas);
    appendField(block, 'description', cols.description);
    appendField(block, 'expected', cols.expected);
    appendField(block, 'performance', cols.performance);
    appendField(block, 'period', cols.period);

    pushAmount(block.mooeList, cols.mooe);
    pushAmount(block.coList, cols.co);
    pushAmount(block.totalList, cols.total);

    if (cols.mooe && !block.mooe) {
        block.mooe = cols.mooe;
    }
    if (cols.co && !block.co) {
        block.co = cols.co;
    }
    if (cols.total && !block.total) {
        block.total = cols.total;
    }
    if (cols.person) {
        block.person = normalizePersonLabel(cols.person);
    }
    if (cols.page) {
        block.page = block.page || cols.page;
    }
    appendField(block, 'sourceText', cols.fullLine);
}

function appendYouthPpas(block, ppas) {
    const raw = compact(ppas);
    if (!raw) {
        return;
    }

    const text = stripYouthLetter(
        raw.replace(/^[\u2022\u25CF\u25E6\uF0B7\uF0D6\uF0A7\uF0B8\u00B7•►▪▫‣⁃\-–—]\s*/, ''),
    );
    if (!text || text === block.programName) {
        return;
    }

    if (!block.ppas) {
        block.ppas = text;
        return;
    }

    if (block.ppas.includes(text)) {
        return;
    }

    if (startsWithBullet(raw)) {
        block.ppas = `${block.ppas} • ${text}`;
        return;
    }

    appendField(block, 'ppas', text);
}

function createEmptyBlock(letter, programName, parentProgram) {
    return {
        letter: letter || '',
        programName: programName || '',
        parentProgram: parentProgram || '',
        ppas: '',
        description: '',
        expected: '',
        performance: '',
        period: '',
        mooe: '',
        co: '',
        total: '',
        mooeList: [],
        coList: [],
        totalList: [],
        person: '',
        page: null,
        sourceText: '',
    };
}

function youthBlockHasContent(block) {
    if (!block) {
        return false;
    }

    return Boolean(
        block.ppas
        || block.mooe
        || block.total
        || block.co
        || (block.mooeList && block.mooeList.length)
        || (block.totalList && block.totalList.length),
    );
}

function youthCategorySection(letter, name) {
    const clean = stripYouthLetter(name);
    if (!letter) {
        return clean || '';
    }
    if (!clean) {
        return letter;
    }

    return `${letter}. ${clean}`;
}

function formatIncludedNote(amount) {
    const raw = String(amount || '').replace(/,/g, '').trim();
    if (!raw) {
        return '';
    }

    const value = Number.parseFloat(raw);
    if (!Number.isFinite(value)) {
        return '';
    }

    return `Included in ₱${value.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function buildStructuredTagRow(tag, fields) {
    return tag + Object.keys(fields).map((key) => `${key}:${fields[key] || ''}`).join('|');
}

function annotateBudget(block) {
    const result = validateBudgetTriple(block.mooe, block.co, block.total);
    block.validationStatus = result.status;
    block.validationMessage = result.message;
    block.manualReviewRequired = result.manualReviewRequired;
    return block;
}

function isGeneralExpenditurePrimaryRow(cols, generalBlock) {
    const text = compact(cols?.ppas);
    if (!text || startsWithBullet(text) || /^[A-J]\.\s/i.test(text) || /^[IVX]+\.\s/i.test(text)) {
        return false;
    }

    if (isCategoryHeading(text, text)) {
        return false;
    }

    const hasOwnBudget = Boolean(cols?.mooe || cols?.co || cols?.total);
    if (!generalBlock) {
        return hasOwnBudget || (text.length >= 3 && text.length <= 90);
    }

    const isDistinctName = text !== generalBlock.ppas
        && !String(generalBlock.ppas || '').includes(text)
        && !text.includes(generalBlock.ppas || '');

    return hasOwnBudget && isDistinctName;
}

function isCategoryHeading(fullLine, ppas) {
    const source = compact(ppas || fullLine);
    return /GENERAL ADMINISTRATION PROGRAM|CURRENT OPERATING EXPENDITURES|Maintenance and Other Operating|Capital Outlay|SK YOUTH DEVELOPMENT AND EMPOWERMENT/i.test(source);
}

function isNewYouthActivity(cols, youthBlock) {
    if (!isActivityRow(cols, 'youth')) {
        return false;
    }

    const ppas = compact(cols.ppas);
    if (!ppas || extractYouthLetter(ppas, cols.fullLine)) {
        return false;
    }

    if (startsWithBullet(ppas)) {
        return true;
    }

    if (!youthBlock) {
        return Boolean(ppas);
    }

    const looksLikeNewName = ppas.length >= 6
        && ppas !== youthBlock.ppas
        && !youthBlock.ppas.includes(ppas)
        && !ppas.includes(youthBlock.programName || '');

    if (looksLikeNewName && (cols.mooe || cols.co || cols.total || youthBlock.mooe || youthBlock.total)) {
        return true;
    }

    return false;
}

function extractAmountFromText(text) {
    const decimalMatches = String(text || '').match(/[\d,]+\.\d{2}/g);
    if (decimalMatches && decimalMatches.length) {
        return normalizeCurrency(decimalMatches[decimalMatches.length - 1]);
    }

    const groupedMatches = String(text || '').match(/\d{1,3}(?:,\d{3})+/g);
    if (groupedMatches && groupedMatches.length) {
        return normalizeCurrency(groupedMatches[groupedMatches.length - 1]);
    }

    return '';
}

function appendSignatureMetadata(lines) {
    const preparedIdx = lines.findIndex((line) => /Prepared\s+by/i.test(line));
    if (preparedIdx < 0) {
        return;
    }

    const blockLines = lines.slice(preparedIdx, Math.min(preparedIdx + 10, lines.length));
    const blockText = blockLines.join('\n');
    const names = [];
    const nameRegex = /HON\.?\s*([A-Z][A-Za-z.\s]+?)(?=\s+HON\.|\s+SK\s+Chair|\s+Barangay\s+Chair|\n|$)/gi;
    let nameMatch;

    while ((nameMatch = nameRegex.exec(blockText)) !== null) {
        names.push(compact(`HON. ${nameMatch[1]}`));
    }

    const fields = {};
    if (names[0]) {
        fields.PREPARED_NAME = names[0];
    }
    if (/SK\s+Chair/i.test(blockText)) {
        fields.PREPARED_POS = 'SK Chairperson';
    }
    if (names[1]) {
        fields.APPROVED_NAME = names[1];
    }
    if (/Barangay\s+Chair/i.test(blockText)) {
        fields.APPROVED_POS = 'Barangay Chairman';
    }

    if (Object.keys(fields).length) {
        lines.push(buildStructuredTagRow('@ABYIP_SIGNATURE@', fields));
    }
}

function extractDocumentMetadata(lines) {
    const text = lines.filter((line) => !line.startsWith('@')).join('\n');
    const normalized = compact(text);
    const document = {
        country: /Republic of the Philippines/i.test(text) ? 'Republic of the Philippines' : null,
        region: null,
        province: null,
        municipality: null,
        barangay_name: null,
        document_title: null,
        sk_council_name: null,
        fiscal_year: null,
        barangay_estimated_budget: null,
        sk_fund_percentage: null,
        sk_fund_amount: null,
        prepared_by_name: null,
        prepared_by_position: null,
        approved_by_name: null,
        approved_by_position: null,
    };

    const regionMatch = normalized.match(/Region\s*([IVX]+-?[A-Z]*)/i);
    if (regionMatch) {
        document.region = `Region ${regionMatch[1]}`;
    }

    const provinceMatch = normalized.match(/Province\s*of\s*([A-Za-z\s]+?)(?:\s+Municipality|\s+BARANGAY|$)/i);
    if (provinceMatch) {
        document.province = compact(provinceMatch[1]);
    }

    const municipalityMatch = normalized.match(/Municipality\s*of\s*([A-Za-z\s]+?)(?:\s+BARANGAY|$)/i);
    if (municipalityMatch) {
        document.municipality = compact(municipalityMatch[1]);
    }

    const barangayMatch = text.match(/BARANGAY\s+([A-Za-z\s]+?)(?:\s+SANGGUNIANG|\s+ANNUAL|$)/im);
    if (barangayMatch) {
        document.barangay_name = compact(barangayMatch[1]);
    }

    const councilMatch = text.match(/SANGGUNIANG\s+KABATAAN\s+NG\s+([A-Za-z\s]+)/i);
    if (councilMatch) {
        document.sk_council_name = `SANGGUNIANG KABATAAN NG ${compact(councilMatch[1])}`;
    }

    if (/ANNUAL BARANGAY YOUTH INVESTMENT PROGRAM/i.test(text)) {
        document.document_title = 'Annual Barangay Youth Investment Program (ABYIP)';
    }

    const yearMatch = text.match(/\bCY\s*(\d{4})\b/i) || text.match(/Fiscal\s+Year\s*[:\-]?\s*(\d{4})/i);
    if (yearMatch) {
        document.fiscal_year = Number.parseInt(yearMatch[1], 10);
    }

    lines.forEach((line) => {
        if (line.startsWith('@ABYIP_HEADER@')) {
            const body = line.slice('@ABYIP_HEADER@'.length);
            body.split('|').forEach((part) => {
                const [key, ...rest] = part.split(':');
                const value = rest.join(':');
                if (key === 'BARANGAY_BUDGET') {
                    document.barangay_estimated_budget = normalizeCurrency(value);
                } else if (key === 'SK_FUND_PERCENT') {
                    document.sk_fund_percentage = value;
                } else if (key === 'SK_FUND_AMOUNT') {
                    document.sk_fund_amount = normalizeCurrency(value);
                }
            });
        }

        if (line.startsWith('@ABYIP_SIGNATURE@')) {
            const body = line.slice('@ABYIP_SIGNATURE@'.length);
            body.split('|').forEach((part) => {
                const [key, ...rest] = part.split(':');
                const value = rest.join(':');
                if (key === 'PREPARED_NAME') {
                    document.prepared_by_name = value;
                } else if (key === 'PREPARED_POS') {
                    document.prepared_by_position = value;
                } else if (key === 'APPROVED_NAME') {
                    document.approved_by_name = value;
                } else if (key === 'APPROVED_POS') {
                    document.approved_by_position = value;
                }
            });
        }
    });

    return document;
}

function rowsFromBlocks(lines) {
    const rows = [];
    let sortOrder = 0;
    let currentCategory = null;
    let currentProgram = null;

    lines.forEach((line) => {
        if (line.startsWith('@ABYIP_CATEGORY@')) {
            const fields = parseTagFields(line, '@ABYIP_CATEGORY@');
            const name = fields.NAME || null;
            const type = (fields.TYPE || '').toLowerCase();
            if (type === 'program' || isProgramHeading(name, null)) {
                currentProgram = name;
            }
            currentCategory = name;
            rows.push({
                row_type: 'category',
                code: null,
                hierarchy_level: type === 'program' || isProgramHeading(name, null) ? 'program' : 'category',
                category: currentCategory,
                program_name: type === 'program' || isProgramHeading(name, null) ? name : (currentProgram || name),
                activity_name: null,
                page_number: fields.PAGE ? Number.parseInt(fields.PAGE, 10) : null,
                source_text: fields.SOURCE || currentCategory,
                sort_order: sortOrder,
                validation_status: 'valid',
                manual_review_required: false,
            });
            sortOrder += 1;
            return;
        }

        if (line.startsWith('@ABYIP_ROW@')) {
            const fields = parseTagFields(line, '@ABYIP_ROW@');
            const hasBudget = Boolean(fields.MOOE || fields.CO || fields.TOTAL);
            const budget = hasBudget
                ? validateBudgetTriple(fields.MOOE, fields.CO, fields.TOTAL)
                : { status: 'valid', message: null, manualReviewRequired: false };
            rows.push({
                row_type: 'expenditure',
                code: fields.CODE || null,
                category: fields.CATEGORY || currentCategory,
                program_name: fields.PROGRAM || currentProgram || fields.PPAS || null,
                activity_name: fields.PPAS || null,
                description: fields.DESC || null,
                expected_result: fields.EXP || null,
                performance_indicator: fields.PERF || null,
                implementation_period: fields.PERIOD || null,
                person_responsible: fields.PERSON || null,
                mooe: fields.MOOE || null,
                co: fields.CO || null,
                total: fields.TOTAL || null,
                page_number: fields.PAGE ? Number.parseInt(fields.PAGE, 10) : null,
                source_text: fields.SOURCE || fields.PPAS || null,
                sort_order: sortOrder,
                validation_status: budget.status,
                validation_message: budget.message || null,
                manual_review_required: budget.manualReviewRequired,
            });
            sortOrder += 1;
            return;
        }

        if (line.startsWith('@YOUTH_ROW@')) {
            const fields = parseTagFields(line, '@YOUTH_ROW@');
            const letter = (fields.LETTER || '').toUpperCase();
            const programName = fields.PROGRAM || null;
            const parentProgram = fields.PARENT || currentCategory;
            const categoryName = fields.CATEGORY || programName;
            const activityName = fields.PPAS || null;
            const isProgramHeader = Boolean(letter && programName && !activityName);

            if (isProgramHeader || (letter && programName && programName !== currentProgram)) {
                currentProgram = programName;
                rows.push({
                    row_type: 'youth_program',
                    code: letter,
                    category: parentProgram,
                    program_name: programName,
                    activity_name: null,
                    description: fields.DESC || null,
                    expected_result: fields.EXP || null,
                    performance_indicator: fields.PERF || null,
                    implementation_period: fields.PERIOD || null,
                    person_responsible: fields.PERSON || null,
                    page_number: fields.PAGE ? Number.parseInt(fields.PAGE, 10) : null,
                    source_text: fields.SOURCE || programName,
                    sort_order: sortOrder,
                    validation_status: 'valid',
                    manual_review_required: false,
                });
                sortOrder += 1;
            }

            if (activityName) {
                const grouped = fields.GROUPED === '1';
                const includedIn = fields.INCLUDED || '';
                const budget = grouped
                    ? { status: 'valid', message: includedIn, manualReviewRequired: false }
                    : validateBudgetTriple(fields.MOOE, fields.CO, fields.TOTAL);
                rows.push({
                    row_type: 'activity',
                    code: letter,
                    category: categoryName,
                    program_name: parentProgram || programName || currentProgram,
                    activity_name: activityName,
                    description: fields.DESC || null,
                    expected_result: fields.EXP || null,
                    performance_indicator: fields.PERF || null,
                    implementation_period: fields.PERIOD || null,
                    person_responsible: fields.PERSON || null,
                    mooe: grouped ? null : (fields.MOOE || null),
                    co: grouped ? null : (fields.CO || null),
                    total: grouped ? null : (fields.TOTAL || null),
                    grouped_budget: grouped,
                    included_in: grouped ? includedIn : null,
                    page_number: fields.PAGE ? Number.parseInt(fields.PAGE, 10) : null,
                    source_text: fields.SOURCE || activityName,
                    sort_order: sortOrder,
                    validation_status: budget.status,
                    validation_message: grouped ? includedIn : (budget.message || null),
                    manual_review_required: grouped ? false : budget.manualReviewRequired,
                });
                sortOrder += 1;
            }
        }
    });

    return rows;
}

function parseTagFields(line, tag) {
    const fields = {};
    String(line || '').slice(tag.length).split('|').forEach((part) => {
        const index = part.indexOf(':');
        if (index === -1) {
            return;
        }
        fields[part.slice(0, index)] = part.slice(index + 1);
    });
    return fields;
}

function flushGeneralBlock(block, lines) {
    if (!block || !block.ppas) {
        return;
    }

    annotateBudget(block);
    lines.push(buildStructuredTagRow('@ABYIP_ROW@', {
        PPAS: block.ppas,
        PROGRAM: block.parentProgram || '',
        CATEGORY: block.programName || '',
        DESC: block.description || '',
        EXP: block.expected || '',
        PERF: block.performance || '',
        PERIOD: block.period || '',
        MOOE: block.mooe || '',
        CO: block.co || '',
        TOTAL: block.total || '',
        PERSON: block.person || '',
        PAGE: block.page || '',
        SOURCE: block.sourceText || block.ppas,
        REVIEW: block.manualReviewRequired ? '1' : '0',
        STATUS: block.validationStatus || '',
    }));
}

function flushYouthBlock(block, lines, asProgramOnly) {
    if (!block || !block.letter) {
        return;
    }

    if (!asProgramOnly && !block.ppas && !block.programName) {
        return;
    }

    if (!asProgramOnly && block.grouped) {
        block.validationStatus = 'valid';
        block.validationMessage = block.includedIn || '';
        block.manualReviewRequired = false;
    } else {
        annotateBudget(block);
    }

    const categorySection = youthCategorySection(block.letter, block.programName);
    lines.push(buildStructuredTagRow('@YOUTH_ROW@', {
        LETTER: block.letter,
        PARENT: block.parentProgram || '',
        CATEGORY: categorySection,
        PROGRAM: categorySection,
        PPAS: asProgramOnly ? '' : (block.ppas || ''),
        DESC: block.description || '',
        EXP: block.expected || '',
        PERF: block.performance || '',
        PERIOD: block.period || '',
        MOOE: asProgramOnly || block.grouped ? '' : (block.mooe || ''),
        CO: asProgramOnly || block.grouped ? '' : (block.co || ''),
        TOTAL: asProgramOnly || block.grouped ? '' : (block.total || ''),
        PERSON: block.person || '',
        PAGE: block.page || '',
        SOURCE: block.sourceText || block.ppas || block.programName,
        GROUPED: block.grouped ? '1' : '0',
        INCLUDED: block.grouped ? (block.includedIn || '') : '',
        REVIEW: block.manualReviewRequired ? '1' : '0',
        STATUS: block.validationStatus || '',
    }));
}

function budgetKey(block) {
    const mooe = block.mooe || '';
    const total = block.total || '';
    if (!mooe && !total && !block.co) {
        return '';
    }

    return `${mooe}|${block.co || '0.00'}|${total || mooe}`;
}

function applyGroupedBudgets(blocks) {
    if (blocks.length <= 1) {
        return blocks;
    }

    const unique = [...new Set(blocks.map((block) => budgetKey(block)).filter(Boolean))];

    if (unique.length === 1) {
        const [mooe, co, total] = unique[0].split('|');
        let assigned = false;
        blocks.forEach((block, index) => {
            const included = looksLikeIncludedBudget(`${block.ppas} ${block.sourceText} ${block.description}`);
            if (included || assigned) {
                block.mooe = '';
                block.co = '';
                block.total = '';
                block.grouped = true;
                return;
            }

            if (budgetKey(block) === unique[0] || index === 0) {
                block.mooe = mooe;
                block.co = co || '0.00';
                block.total = total || mooe;
                assigned = true;
                return;
            }

            block.mooe = '';
            block.co = '';
            block.total = '';
            block.grouped = true;
        });
        return annotateGroupedNotes(blocks);
    }

    const seen = new Set();
    blocks.forEach((block) => {
        if (looksLikeIncludedBudget(`${block.ppas} ${block.sourceText} ${block.description}`)) {
            block.mooe = '';
            block.co = '';
            block.total = '';
            block.grouped = true;
            return;
        }

        const key = budgetKey(block);
        if (!key) {
            return;
        }
        if (seen.has(key)) {
            block.mooe = '';
            block.co = '';
            block.total = '';
            block.grouped = true;
            return;
        }
        seen.add(key);
    });

    return annotateGroupedNotes(blocks);
}

function annotateGroupedNotes(blocks) {
    const owner = blocks.find((block) => !block.grouped && budgetKey(block));
    const note = owner ? formatIncludedNote(owner.mooe || owner.total) : '';

    blocks.forEach((block) => {
        if (!block.grouped) {
            return;
        }

        block.includedIn = note;
        block.validationStatus = 'valid';
        block.validationMessage = note;
        block.manualReviewRequired = false;
    });

    return blocks;
}

function collapseDuplicateAmountStack(amounts) {
    if (amounts.length < 2 || amounts.length % 2 !== 0) {
        return amounts;
    }

    const half = amounts.length / 2;
    const first = amounts.slice(0, half);
    const second = amounts.slice(half);
    if (first.every((value, index) => value === second[index])) {
        return first;
    }

    return amounts;
}

function uniqueAmountTriples(block) {
    const mooeList = collapseDuplicateAmountStack(
        (block.mooeList && block.mooeList.length ? block.mooeList : (block.mooe ? [block.mooe] : []))
            .slice(),
    );
    const totalList = collapseDuplicateAmountStack(
        (block.totalList && block.totalList.length ? block.totalList : (block.total ? [block.total] : []))
            .slice(),
    );
    const coList = (block.coList && block.coList.length ? block.coList : (block.co ? [block.co] : [])).slice();
    const count = Math.max(mooeList.length, totalList.length, coList.length);
    const triples = [];

    for (let index = 0; index < count; index += 1) {
        const mooe = mooeList[index] || '';
        const total = totalList[index] || mooe;
        let co = coList[index] || '';
        if (!co && mooe && total && mooe === total) {
            co = '0.00';
        }
        triples.push({ mooe, co, total });
    }

    const merged = [];
    triples.forEach((triple) => {
        const last = merged[merged.length - 1];
        const amount = triple.total || triple.mooe;
        const lastAmount = last ? (last.total || last.mooe) : '';
        if (last && amount && amount === lastAmount) {
            last.mooe = last.mooe || triple.mooe;
            last.co = last.co || triple.co || '0.00';
            last.total = last.total || triple.total;
            return;
        }
        merged.push({ ...triple });
    });

    return merged;
}

function expandYouthGroup(group) {
    const expanded = [];
    group.forEach((block) => {
        let names = splitBulletActivities(block.ppas);
        const triples = uniqueAmountTriples(block);

        if (names.length === 0 && (triples.length || block.programName)) {
            names = [block.programName].filter(Boolean);
        }

        if (names.length <= 1) {
            if (names[0]) {
                block.ppas = names[0];
            }
            if (triples[0]) {
                block.mooe = triples[0].mooe;
                block.co = triples[0].co;
                block.total = triples[0].total;
            }
            expanded.push(block);
            return;
        }

        names.forEach((name, index) => {
            const copy = {
                ...block,
                ppas: name,
                mooeList: [],
                coList: [],
                totalList: [],
            };

            if (triples.length === names.length) {
                copy.mooe = triples[index].mooe;
                copy.co = triples[index].co;
                copy.total = triples[index].total;
                copy.grouped = false;
            } else if (triples.length === 1) {
                if (index === 0) {
                    copy.mooe = triples[0].mooe;
                    copy.co = triples[0].co;
                    copy.total = triples[0].total;
                    copy.grouped = false;
                } else {
                    copy.mooe = '';
                    copy.co = '';
                    copy.total = '';
                    copy.grouped = true;
                }
            } else if (triples[index]) {
                copy.mooe = triples[index].mooe;
                copy.co = triples[index].co;
                copy.total = triples[index].total;
                copy.grouped = false;
            } else {
                copy.mooe = '';
                copy.co = '';
                copy.total = '';
                copy.grouped = true;
            }

            expanded.push(copy);
        });
    });

    return applyGroupedBudgets(expanded);
}

function flushYouthGroup(group, lines) {
    expandYouthGroup(group).forEach((block) => {
        flushYouthBlock(block, lines);
    });
}

export async function extractPdfDocument(pdfDoc) {
    const lines = [];
    const pageRows = [];
    const allItems = [];
    let inYouthSection = false;
    let inExpenditureSection = false;
    let inReceiptsSection = true;
    let budgetColumn = 'mooe';
    let generalBlock = null;
    let youthProgramEmitted = {};
    let currentProgramName = '';
    let currentCategoryName = '';
    let lastHeaderCenters = null;

    for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum += 1) {
        const page = await pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: 1 });
        const textContent = await page.getTextContent();
        const items = [];

        textContent.items.forEach((item) => {
            const text = compact(item.str);
            if (!text) {
                return;
            }

            items.push({
                text,
                x: item.transform[4],
                y: item.transform[5],
                width: item.width || 0,
                page: pageNum,
            });
        });
        items.forEach((item) => {
            allItems.push(item);
        });

        clusterItemsIntoRows(items).forEach((row) => {
            pageRows.push({ width: viewport.width, row, page: pageNum });
        });
    }

    const columnBounds = detectColumnBounds(pageRows);
    lastHeaderCenters = detectBudgetHeaderCenters(pageRows)?.centers || null;

    pageRows.forEach((entry) => {
        const { width, page } = entry;
        const pageCenters = detectBudgetHeaderCenters([entry])?.centers;
        if (pageCenters) {
            lastHeaderCenters = pageCenters;
        }
        const cols = parseRowColumns(entry.row, width, columnBounds, budgetColumn, lastHeaderCenters);
        const fullLine = cols.fullLine;
        cols.page = page;

        if (!fullLine) {
            return;
        }

        if (isRepeatedHeaderLine(fullLine, page) && !/Barangay\s+Estimated\s+Budget|Sangguniang\s+Kabataan\s+Fund/i.test(fullLine)) {
            return;
        }

        if (/I\.\s*RECEIPTS/i.test(fullLine)) {
            inReceiptsSection = true;
            inExpenditureSection = false;
            currentProgramName = compact(fullLine.replace(/^[IVX]+\.\s*/i, ''));
            currentCategoryName = compact(fullLine);
            lines.push(buildStructuredTagRow('@ABYIP_CATEGORY@', {
                TYPE: 'program',
                NAME: currentProgramName,
                PAGE: page,
                SOURCE: fullLine,
            }));
            lines.push(buildStructuredTagRow('@ABYIP_CATEGORY@', {
                TYPE: 'category',
                NAME: currentCategoryName,
                PARENT: currentProgramName,
                PAGE: page,
                SOURCE: fullLine,
            }));
            lines.push(fullLine);
            return;
        }

        if (/II\.\s*EXPENDITURE/i.test(fullLine)) {
            inExpenditureSection = true;
            inReceiptsSection = false;
            currentProgramName = '';
            currentCategoryName = 'CURRENT OPERATING EXPENDITURES';
            lines.push(fullLine);
            return;
        }

        if (/Barangay\s+Estimated\s+Budget/i.test(fullLine)) {
            const amount = extractAmountFromText(fullLine);
            if (amount) {
                lines.push(`@ABYIP_HEADER@BARANGAY_BUDGET:${amount}`);
            }
            lines.push(fullLine);
            return;
        }

        if (/Sangguniang\s+Kabataan\s+Fund/i.test(fullLine)) {
            const pctMatch = fullLine.match(/(\d+(?:\.\d+)?)\s*%/);
            const amount = extractAmountFromText(fullLine);
            let headerTag = '@ABYIP_HEADER@';
            if (pctMatch) {
                headerTag += `SK_FUND_PERCENT:${pctMatch[1]}`;
            }
            if (amount) {
                headerTag += `${pctMatch ? '|' : ''}SK_FUND_AMOUNT:${amount}`;
            }
            if (headerTag !== '@ABYIP_HEADER@') {
                lines.push(headerTag);
            }
            lines.push(fullLine);
            return;
        }

        if (/GENERAL ADMINISTRATION PROGRAM/i.test(fullLine) && !cols.mooe && !cols.total) {
            budgetColumn = 'mooe';
            currentProgramName = 'GENERAL ADMINISTRATION PROGRAM';
            lines.push(buildStructuredTagRow('@ABYIP_CATEGORY@', {
                TYPE: 'program',
                NAME: currentProgramName,
                PAGE: page,
                SOURCE: fullLine,
            }));
            if (/CURRENT OPERATING EXPENDITURES/i.test(fullLine)) {
                currentCategoryName = 'CURRENT OPERATING EXPENDITURES';
                if (generalBlock) {
                    generalBlock.parentProgram = currentProgramName;
                    generalBlock.programName = currentCategoryName;
                }
                lines.push(buildStructuredTagRow('@ABYIP_CATEGORY@', {
                    TYPE: 'category',
                    NAME: currentCategoryName,
                    PARENT: currentProgramName,
                    PAGE: page,
                    SOURCE: fullLine,
                }));
            }
            lines.push(fullLine);
            return;
        }

        if (/CURRENT OPERATING EXPENDITURES/i.test(fullLine) && !cols.mooe && !cols.total) {
            budgetColumn = 'mooe';
            currentCategoryName = 'CURRENT OPERATING EXPENDITURES';
            if (generalBlock) {
                generalBlock.parentProgram = currentProgramName || generalBlock.parentProgram;
                generalBlock.programName = currentCategoryName;
            }
            lines.push(buildStructuredTagRow('@ABYIP_CATEGORY@', {
                TYPE: 'category',
                NAME: currentCategoryName,
                PARENT: currentProgramName,
                PAGE: page,
                SOURCE: fullLine,
            }));
            lines.push(fullLine);
            return;
        }

        if (/Maintenance and Other Operating/i.test(fullLine) && !cols.mooe && !cols.total) {
            budgetColumn = 'mooe';
            flushGeneralBlock(generalBlock, lines);
            generalBlock = null;
            currentCategoryName = compact(cols.ppas || fullLine);
            lines.push(buildStructuredTagRow('@ABYIP_CATEGORY@', {
                TYPE: 'category',
                NAME: currentCategoryName,
                PARENT: currentProgramName,
                PAGE: page,
                SOURCE: fullLine,
            }));
            lines.push(fullLine);
            return;
        }

        if (/^Capital Outlay\b/i.test(fullLine) && !cols.mooe && !cols.total) {
            budgetColumn = 'co';
            flushGeneralBlock(generalBlock, lines);
            generalBlock = null;
            currentCategoryName = compact(cols.ppas || fullLine);
            lines.push(buildStructuredTagRow('@ABYIP_CATEGORY@', {
                TYPE: 'category',
                NAME: currentCategoryName,
                PARENT: currentProgramName,
                PAGE: page,
                SOURCE: fullLine,
            }));
            lines.push(fullLine);
            return;
        }

        if (/SK\s+YOUTH\s+DEVELOPMENT/i.test(fullLine)) {
            flushGeneralBlock(generalBlock, lines);
            generalBlock = null;
            inYouthSection = true;
            inExpenditureSection = true;
            inReceiptsSection = false;
            currentProgramName = compact(cols.ppas || fullLine);
            currentCategoryName = currentProgramName;
            if (!currentProgramName || currentProgramName !== youthProgramEmitted.__parent) {
                lines.push(buildStructuredTagRow('@ABYIP_CATEGORY@', {
                    TYPE: 'program',
                    NAME: currentProgramName,
                    PAGE: page,
                    SOURCE: fullLine,
                }));
                youthProgramEmitted.__parent = currentProgramName;
            }
            lines.push(fullLine);
            return;
        }

        if (!inYouthSection && (isProgramHeading(fullLine, cols) || /GENERAL ADMINISTRATION PROGRAM/i.test(fullLine))) {
            const headingName = compact(cols.ppas || fullLine);
            if (headingName && headingName === currentProgramName) {
                lines.push(fullLine);
                return;
            }
            flushGeneralBlock(generalBlock, lines);
            generalBlock = null;
            currentProgramName = headingName;
            currentCategoryName = currentProgramName;
            lines.push(buildStructuredTagRow('@ABYIP_CATEGORY@', {
                TYPE: 'program',
                NAME: currentProgramName,
                PAGE: page,
                SOURCE: fullLine,
            }));
            lines.push(fullLine);
            return;
        }

        if (isSignatureLine(fullLine)) {
            lines.push(fullLine);
            return;
        }

        if (/^TOTAL\b/i.test(fullLine) && inYouthSection) {
            inYouthSection = false;
            const totalAmounts = fullLine.match(/[\d,]+\.\d{2}/g);
            if (totalAmounts && totalAmounts.length) {
                lines.push(`@ABYIP_GRAND_TOTAL@${totalAmounts[totalAmounts.length - 1].replace(/,/g, '')}`);
            }
            lines.push(fullLine);
            return;
        }

        if (inReceiptsSection && !inExpenditureSection) {
            if (/10%\s+of\s+the\s+General\s+Fund/i.test(fullLine)) {
                const ppaMatch = fullLine.match(/10%\s+of\s+the\s+General\s+Fund[^\n|]*/i);
                lines.push(buildStructuredTagRow('@ABYIP_ROW@', {
                    PPAS: compact(ppaMatch ? ppaMatch[0] : (cols.ppas || fullLine)),
                    PROGRAM: currentProgramName,
                    CATEGORY: currentCategoryName,
                    DESC: '',
                    EXP: '',
                    PERF: '',
                    PERIOD: '',
                    MOOE: '',
                    CO: '',
                    TOTAL: '',
                    PERSON: '',
                    PAGE: page,
                    SOURCE: fullLine,
                    REVIEW: '0',
                    STATUS: 'valid',
                }));
            }
            lines.push(fullLine);
            return;
        }

        if (inYouthSection) {
            lines.push(fullLine);
            return;
        }

        if (inExpenditureSection && !isCategoryHeading(fullLine, cols.ppas)) {
            const isPrimary = isGeneralExpenditurePrimaryRow(cols, generalBlock);
            if (isPrimary) {
                flushGeneralBlock(generalBlock, lines);
                generalBlock = createEmptyBlock('', currentCategoryName, currentProgramName);
                mergeRowIntoBlock(generalBlock, cols);
            } else if (hasStructuredTableData(cols) || cols.person) {
                if (generalBlock) {
                    mergeRowIntoBlock(generalBlock, cols);
                } else {
                    generalBlock = createEmptyBlock('', currentCategoryName, currentProgramName);
                    mergeRowIntoBlock(generalBlock, cols);
                }
            }
        }

        lines.push(fullLine);
    });

    flushGeneralBlock(generalBlock, lines);

    const youthParent = /YOUTH/i.test(currentProgramName)
        ? currentProgramName
        : (youthProgramEmitted.__parent || 'SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS');
    reconstructYouthBlocks(allItems, lastHeaderCenters, youthParent).forEach((block) => {
        if (!youthProgramEmitted[block.letter]) {
            flushYouthBlock({
                ...block,
                ppas: '',
                mooe: '',
                co: '',
                total: '',
                grouped: false,
            }, lines, true);
            youthProgramEmitted[block.letter] = block.programName;
        }
        flushYouthGroup([block], lines);
    });

    appendSignatureMetadata(lines);

    const extractedText = lines.join('\n');
    const document = extractDocumentMetadata(lines);
    const rows = rowsFromBlocks(lines);
    const reviewCount = rows.filter((row) => row.manual_review_required).length;

    return {
        extractedText,
        document,
        rows,
        stats: {
            pages: pdfDoc.numPages,
            rowsDetected: rows.length,
            rowsRequiringReview: reviewCount,
            budgetWarnings: rows.filter((row) => row.validation_status === 'warning').length,
        },
    };
}

export async function extractPdfTextForPrograms(pdfDoc) {
    const result = await extractPdfDocument(pdfDoc);
    return result.extractedText;
}
