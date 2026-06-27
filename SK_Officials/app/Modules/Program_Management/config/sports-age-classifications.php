<?php

/**
 * Default age classifications per sports discipline.
 * Basketball and Volleyball use different division names and brackets.
 * "Other" has no defaults — officials add their own classifications in the form.
 */
return [
    'basketball' => [
        ['id' => 'cls_mosquito_division', 'name' => 'Mosquito Division', 'min_age' => 15, 'max_age' => 17],
        ['id' => 'cls_midget_division', 'name' => 'Midget Division', 'min_age' => 18, 'max_age' => 21],
        ['id' => 'cls_junior_division', 'name' => 'Junior Division', 'min_age' => 22, 'max_age' => 25],
        ['id' => 'cls_senior_division', 'name' => 'Senior Division', 'min_age' => 26, 'max_age' => 30],
    ],
    'volleyball' => [
        ['id' => 'cls_youth_division', 'name' => 'Youth Division', 'min_age' => 15, 'max_age' => 17],
        ['id' => 'cls_cadet_division', 'name' => 'Cadet Division', 'min_age' => 18, 'max_age' => 20],
        ['id' => 'cls_intermediate_division', 'name' => 'Intermediate Division', 'min_age' => 21, 'max_age' => 23],
        ['id' => 'cls_open_division', 'name' => 'Open Division', 'min_age' => 24, 'max_age' => 26],
        ['id' => 'cls_senior_kk_division', 'name' => 'Senior KK Division', 'min_age' => 27, 'max_age' => 30],
    ],
    'other' => [],
];
