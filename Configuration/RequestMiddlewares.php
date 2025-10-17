<?php

return [
    'frontend' => [
        'Skynetaccessibilityscanner-frontend' => [
            'target' => \Skynettechnologies\Skynetaccessibilityscanner\Middleware\AwesomeMiddleware::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ]
    ]
];
