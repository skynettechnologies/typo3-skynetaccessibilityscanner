<?php

$EM_CONF['skynetaccessibilityscanner'] = [
    'title' => 'SkynetAccessibility Scanner®',
    'description' => 'Scan, monitor, and identify website accessibility issues across WCAG 2.0, 2.1, 2.2, ADA, Section 508, EN 301 549, UK Equality Act, Australian DDA, and Canada ACA. Get simple issue highlights with recommended fixes.',
    'category' => 'plugin',
    'author' => 'Skynet Technologies USA LLC',
    'author_email' => 'hello@skynetindia.info',
    'author_company' => 'Skynet Technologies USA LLC',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'version' => '14.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'Skynettechnologies\\Skynetaccessibilityscanner\\' => 'Classes/',
        ],
    ],
];
