<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'anders und sehr: geo redirect',
    'description' => 'Redirect users based on browser language and ip country to the right language version of the website',
    'category' => 'service',
    'author' => 'Matthias Vogel',
    'author_email' => 'm.vogel@andersundsehr.com',
    'state' => 'stable',
    'version' => 'dev-',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0 - 13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
