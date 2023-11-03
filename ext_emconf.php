<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'anders und sehr: geo',
    'description' => '',
    'category' => 'service',
    'author' => 'Matthias Vogel',
    'author_email' => 'm.vogel@andersundsehr.com',
    'state' => 'stable',
    'version' => \Composer\InstalledVersions::getPrettyVersion('andersundsehr/geo_redirect'),
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0 - 12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
