<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'VCF-QR',
    'description' => 'Create QR-Code with VCF card',
    'category' => 'misc',
    'author' => 'Thomas Rawiel',
    'author_email' => 'thomas.rawiel@gmail.com',
    'state' => 'beta',
    'clearCacheOnLoad' => 0,
    'version' => '0.3.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.9.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'tt_address' => '8.0.0-9.99.99',
        ],
    ],
];
