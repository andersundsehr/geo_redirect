<?php

use AUS\GeoRedirect\Middleware\RedirectMiddleware;

return [
    'frontend' => [
        'a-u-s/geo-redirect/redirect-middleware' => [
            'target' => RedirectMiddleware::class,
            'after' => [
                'typo3/cms-core/normalized-params-attribute',
            ],
            'before' => [
                'typo3/cms-frontend/base-redirect-resolver',
                'typo3/cms-frontend/preprocessing',
            ],
        ],
    ],
];
