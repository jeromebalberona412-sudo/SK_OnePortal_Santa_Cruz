<?php

it('renders sports evaluation page with sports program letter', function () {
    $html = view('Program_Management::sports.evaluation')->render();

    expect($html)
        ->toContain('data-program-key="sports"')
        ->toContain('data-program-letter="I"')
        ->toContain('eval-stats-grid')
        ->toContain('btnCreateEvaluation')
        ->toContain('evalTableBody');
});

it('renders scholarship evaluation page with scholarship program letter', function () {
    $html = view('Program_Management::scholarship.evaluation')->render();

    expect($html)
        ->toContain('data-program-key="scholarship"')
        ->toContain('data-program-letter="A"')
        ->toContain('eval-stats-grid')
        ->toContain('btnCreateEvaluation');
});

it('shares evaluation content partial across program types', function () {
    $html = view('Program_Management::partials.program-evaluation-content')->render();

    expect($html)
        ->toContain('evalStatDraft')
        ->toContain('evalStatActive')
        ->toContain('evalStatClosed')
        ->toContain('createEvalModal')
        ->toContain('Evaluation Questions');
});
