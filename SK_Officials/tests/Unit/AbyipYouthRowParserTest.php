<?php

use App\Modules\Barangay_ABYIP\Services\AbyipService;

it('creates one youth activity per tagged pdf row and does not invent names', function () {
    $service = app(AbyipService::class);
    $method = new ReflectionMethod(AbyipService::class, 'parseStructuredYouthRowsFromText');
    $method->setAccessible(true);

    $text = implode("\n", [
        '@YOUTH_ROW@LETTER:A|PROGRAM:Equitable Access to Quality Education|PPAS:|DESC:|MOOE:|CO:|TOTAL:|PAGE:3',
        '@YOUTH_ROW@LETTER:A|PROGRAM:Equitable Access to Quality Education|PPAS:Support to ALS and RIC|DESC:Assistance for learners|MOOE:50000.00|CO:|TOTAL:50000.00|PERSON:Sangguniang Kabataan Council|PAGE:3|SOURCE:Support to ALS and RIC 50,000.00',
        '@YOUTH_ROW@LETTER:A|PROGRAM:Equitable Access to Quality Education|PPAS:150 Students for Educational Assistance|DESC:Educational assistance|MOOE:100000.00|CO:|TOTAL:100000.00|PAGE:3',
    ]);

    $programs = $method->invoke($service, $text);

    expect($programs)->toHaveCount(1)
        ->and($programs[0]['name'])->toBe('A. Equitable Access to Quality Education')
        ->and($programs[0]['activities'])->toHaveCount(2)
        ->and($programs[0]['activities'][0]['ppa_name'])->toBe('Support to ALS and RIC')
        ->and($programs[0]['activities'][0]['budget_mooe'])->toBe('50000.00')
        ->and($programs[0]['activities'][1]['ppa_name'])->toBe('150 Students for Educational Assistance')
        ->and($programs[0]['activities'][1]['budget_mooe'])->toBe('100000.00');
});

it('does not invent youth activities when the pdf has none', function () {
    $service = app(AbyipService::class);
    $method = new ReflectionMethod(AbyipService::class, 'parseStructuredYouthRowsFromText');
    $method->setAccessible(true);

    $programs = $method->invoke($service, '@YOUTH_ROW@LETTER:H|PROGRAM:Feeding Program for KK Members|PPAS:|PAGE:5');

    expect($programs)->toHaveCount(1)
        ->and($programs[0]['activities'])->toBe([]);
});

it('keeps parent program and category separate from the activity name', function () {
    $service = app(AbyipService::class);
    $method = new ReflectionMethod(AbyipService::class, 'parseStructuredYouthRowsFromText');
    $method->setAccessible(true);

    $text = implode("\n", [
        '@YOUTH_ROW@LETTER:C|PARENT:SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS|CATEGORY:Disaster Risk Reduction and Resiliency|PROGRAM:Disaster Risk Reduction and Resiliency|PPAS:|PAGE:4',
        '@YOUTH_ROW@LETTER:C|PARENT:SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS|CATEGORY:Disaster Risk Reduction and Resiliency|PROGRAM:Disaster Risk Reduction and Resiliency|PPAS:Disaster Preparedness Training|MOOE:50000.00|CO:0.00|TOTAL:50000.00|PAGE:4',
    ]);

    $programs = $method->invoke($service, $text);

    expect($programs)->toHaveCount(1)
        ->and($programs[0]['name'])->toBe('C. Disaster Risk Reduction and Resiliency')
        ->and($programs[0]['parent_program'])->toBe('SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS')
        ->and($programs[0]['activities'])->toHaveCount(1)
        ->and($programs[0]['activities'][0]['ppa_name'])->toBe('Disaster Preparedness Training')
        ->and($programs[0]['activities'][0]['activity_name'])->toBe('Disaster Preparedness Training')
        ->and($programs[0]['activities'][0]['category'])->toBe('C. Disaster Risk Reduction and Resiliency')
        ->and($programs[0]['activities'][0]['program_name'])->toBe('SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS')
        ->and($programs[0]['activities'][0]['budget_mooe'])->toBe('50000.00')
        ->and($programs[0]['activities'][0]['budget_co'])->toBe('0.00')
        ->and($programs[0]['activities'][0]['budget_total'])->toBe('50000.00');
});

it('keeps a shared pdf allocation on one activity instead of duplicating it', function () {
    $service = app(AbyipService::class);
    $method = new ReflectionMethod(AbyipService::class, 'parseStructuredYouthRowsFromText');
    $method->setAccessible(true);

    $text = implode("\n", [
        '@YOUTH_ROW@LETTER:B|PROGRAM:Environmental Protection|PPAS:Clean-Up Drive|MOOE:60000.00|CO:0.00|TOTAL:60000.00|PAGE:4|GROUPED:0',
        '@YOUTH_ROW@LETTER:B|PROGRAM:Environmental Protection|PPAS:Payroll for Laborer|MOOE:|CO:|TOTAL:|PAGE:4|GROUPED:1|SOURCE:Payroll for Laborer Included in 60,000.00',
        '@YOUTH_ROW@LETTER:B|PROGRAM:Environmental Protection|PPAS:Tree Planting|MOOE:|CO:|TOTAL:|PAGE:4|GROUPED:1',
    ]);

    $programs = $method->invoke($service, $text);

    expect($programs[0]['activities'])->toHaveCount(3)
        ->and($programs[0]['activities'][0]['ppa_name'])->toBe('Clean-Up Drive')
        ->and($programs[0]['activities'][0]['budget_mooe'])->toBe('60000.00')
        ->and($programs[0]['activities'][0]['budget_total'])->toBe('60000.00')
        ->and($programs[0]['activities'][1]['ppa_name'])->toBe('Payroll for Laborer')
        ->and($programs[0]['activities'][1]['budget_mooe'])->toBeNull()
        ->and($programs[0]['activities'][1]['grouped_budget'])->toBeTrue()
        ->and($programs[0]['activities'][1]['validation_message'])->toBe('Included in ₱60,000.00')
        ->and($programs[0]['activities'][2]['ppa_name'])->toBe('Tree Planting')
        ->and($programs[0]['activities'][2]['budget_mooe'])->toBeNull()
        ->and($programs[0]['budget_mooe'])->toBe(60000.0);
});

it('parses a jan-dec implementation period into start and end dates', function () {
    $service = app(AbyipService::class);
    $method = new ReflectionMethod(AbyipService::class, 'parsePeriodDates');
    $method->setAccessible(true);

    expect($method->invoke($service, 'Jan. 1–Dec. 31, 2025'))->toMatchArray([
        'start' => '2025-01-01',
        'end' => '2025-12-31',
    ]);
});

it('distinguishes program headings from nested categories in structured tags', function () {
    $service = app(AbyipService::class);
    $method = new ReflectionMethod(AbyipService::class, 'parseStructuredAbyipRowsFromText');
    $method->setAccessible(true);

    $text = implode("\n", [
        '@ABYIP_CATEGORY@TYPE:program|NAME:GENERAL ADMINISTRATION PROGRAM|PAGE:1|SOURCE:GENERAL ADMINISTRATION PROGRAM',
        '@ABYIP_CATEGORY@TYPE:category|NAME:CURRENT OPERATING EXPENDITURES|PARENT:GENERAL ADMINISTRATION PROGRAM|PAGE:1',
        '@ABYIP_ROW@PPAS:Honoraria|PROGRAM:GENERAL ADMINISTRATION PROGRAM|CATEGORY:CURRENT OPERATING EXPENDITURES|MOOE:10000.00|CO:0.00|TOTAL:10000.00|PAGE:1|SOURCE:Honoraria 10,000.00',
    ]);

    $items = $method->invoke($service, $text);

    expect($items[0]['hierarchy_level'])->toBe('program')
        ->and($items[0]['ppa_name'])->toBe('GENERAL ADMINISTRATION PROGRAM')
        ->and($items[1]['hierarchy_level'])->toBe('category')
        ->and($items[1]['ppa_name'])->toBe('CURRENT OPERATING EXPENDITURES')
        ->and($items[1]['program_name'])->toBe('GENERAL ADMINISTRATION PROGRAM')
        ->and($items[2]['ppa_name'])->toBe('Honoraria')
        ->and($items[2]['activity_name'])->toBe('Honoraria')
        ->and($items[2]['program_name'])->toBe('GENERAL ADMINISTRATION PROGRAM')
        ->and($items[2]['category'])->toBe('CURRENT OPERATING EXPENDITURES')
        ->and($items[2]['budget_mooe'])->toBe('10000.00')
        ->and($items[2]['budget_co'])->toBe('0.00')
        ->and($items[2]['budget_total'])->toBe('10000.00');
});

it('keeps a receipts fund row without inventing budget amounts', function () {
    $service = app(AbyipService::class);
    $method = new ReflectionMethod(AbyipService::class, 'parseStructuredAbyipRowsFromText');
    $method->setAccessible(true);

    $text = implode("\n", [
        '@ABYIP_CATEGORY@TYPE:program|NAME:Receipts Program|PAGE:1',
        '@ABYIP_CATEGORY@TYPE:category|NAME:I. Receipts Program|PARENT:Receipts Program|PAGE:1',
        '@ABYIP_ROW@PPAS:10% of the General Fund of the Barangay|PROGRAM:Receipts Program|CATEGORY:I. Receipts Program|MOOE:|CO:|TOTAL:|PAGE:1',
    ]);

    $items = $method->invoke($service, $text);

    expect($items[2]['ppa_name'])->toBe('10% of the General Fund of the Barangay')
        ->and($items[2]['program_name'])->toBe('Receipts Program')
        ->and($items[2]['category'])->toBe('I. Receipts Program')
        ->and($items[2]['budget_mooe'])->toBeNull()
        ->and($items[2]['budget_co'])->toBeNull()
        ->and($items[2]['budget_total'])->toBeNull();
});

it('joins wrapped pdf headings and bullet activities without inventing names', function () {
    $service = app(AbyipService::class);
    $method = new ReflectionMethod(AbyipService::class, 'parseYouthProgramBlocksFromText');
    $method->setAccessible(true);

    $text = <<<'TEXT'
SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS
A. Equitable Access to
Quality Education
 Support to ALS and RIC
 150 Students for
Educational Assistance
 Support to Elementary
and Daycare
Provide school supplies to ALS Students
Increased number of youth enrollee in schools
Percentage increase in the number of youth enrollee
January 01, 2026 to
December 31, 2026
12,000.00
150,000.00
13,000.00
12,000.00
150,000.00
13,000.00
Sangguniang
Kabataan
Council/ALS
B. Environmental Protection
 Clean – Up Drive
 Payroll for Laborer
 Tree Planting
Honorarium is given for the proper pay
January 01, 2026 to December 31, 2026
60,000.00
60,000.00
Sangguniang Kabataan Council
H. Feeding Program for KK
Members
Improve the the health and physique of the childrens.
January 01, 2026 to December 31, 2026
15,000.00
15,000.00
Sangguniang Kabataan Council
J. Other Programs
 Katipunan ng Kabataan (KK) General Assembly
 BarangayDay Celebration
 Youth Week
Cost of expenditures to be used
January 01, 2026 to December 31, 2026
30,000.00
17,547.67
20,000.00
30,000.00
17,547.67
20,000.00
Sangguniang Kabataan Council
TOTAL 1,419,946.60
TEXT;

    $programs = $method->invoke($service, $text);
    $byLetter = [];
    foreach ($programs as $program) {
        $byLetter[$program['letter']] = $program;
    }

    expect($byLetter)->toHaveKeys(['A', 'B', 'H', 'J'])
        ->and($byLetter['A']['name'])->toBe('A. Equitable Access to Quality Education')
        ->and(array_column($byLetter['A']['activities'], 'ppa_name'))->toBe([
            'Support to ALS and RIC',
            '150 Students for Educational Assistance',
            'Support to Elementary and Daycare',
        ])
        ->and($byLetter['A']['activities'][0]['budget_mooe'])->toBe('12000.00')
        ->and($byLetter['A']['activities'][1]['budget_mooe'])->toBe('150000.00')
        ->and($byLetter['A']['activities'][2]['budget_mooe'])->toBe('13000.00')
        ->and($byLetter['B']['name'])->toBe('B. Environmental Protection')
        ->and(array_column($byLetter['B']['activities'], 'ppa_name'))->toBe([
            'Clean – Up Drive',
            'Payroll for Laborer',
            'Tree Planting',
        ])
        ->and($byLetter['B']['activities'][0]['budget_mooe'])->toBe('60000.00')
        ->and($byLetter['B']['activities'][1]['grouped_budget'])->toBeTrue()
        ->and($byLetter['B']['activities'][2]['grouped_budget'])->toBeTrue()
        ->and($byLetter['H']['name'])->toBe('H. Feeding Program for KK Members')
        ->and($byLetter['H']['activities'])->toHaveCount(1)
        ->and($byLetter['H']['activities'][0]['ppa_name'])->toBe('Feeding Program for KK Members')
        ->and($byLetter['H']['activities'][0]['budget_mooe'])->toBe('15000.00')
        ->and($byLetter['J']['name'])->toBe('J. Other Programs')
        ->and(array_column($byLetter['J']['activities'], 'ppa_name'))->toBe([
            'Katipunan ng Kabataan (KK) General Assembly',
            'Barangay Day Celebration',
            'Youth Week',
        ])
        ->and($byLetter['J']['activities'][0]['budget_mooe'])->toBe('30000.00')
        ->and($byLetter['J']['activities'][1]['budget_mooe'])->toBe('17547.67')
        ->and($byLetter['J']['activities'][2]['budget_mooe'])->toBe('20000.00');
});

it('does not treat description fragments as youth program headings', function () {
    $service = app(AbyipService::class);
    $method = new ReflectionMethod(AbyipService::class, 'parseYouthProgramBlocksFromText');
    $method->setAccessible(true);

    $text = <<<'TEXT'
SK YOUTH DEVELOPMENT AND EMPOWERMENT PROGRAMS
E. Health
 Medicines/ Medical
Equipment
Campaigning Materials for Anti-Drugs such as Leaflets
January 01, 2026 to December 31, 2026
30,000.00
30,000.00
Sangguniang Kabataan Council/BADAC
F. Anti-Drug and Peace and
Order
 Orientation for Anti-Drug
and Physical Abuse
 Foods and
Accommodations
January 01, 2026 to December 31, 2026
10,000.00
10,000.00
Sangguniang Kabataan Council
TOTAL 1,419,946.60
TEXT;

    $programs = $method->invoke($service, $text);

    expect($programs)->toHaveCount(2)
        ->and($programs[0]['name'])->toBe('E. Health')
        ->and($programs[0]['activities'][0]['ppa_name'])->toBe('Medicines/ Medical Equipment')
        ->and($programs[1]['name'])->toBe('F. Anti-Drug and Peace and Order')
        ->and(array_column($programs[1]['activities'], 'ppa_name'))->toBe([
            'Orientation for Anti-Drug and Physical Abuse',
            'Foods and Accommodations',
        ]);
});
