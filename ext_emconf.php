<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'VCF-QR',
    'description' => 'Create QR-Code with VCF card',
    'category' => 'misc',
    'author' => 'Thomas Rawiel',
    'author_email' => 'thomas.rawiel@gmail.com',
    'state' => 'alpha',
    'clearCacheOnLoad' => 0,
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.9.99',
            'tt_address' => '9.0.0-0.0.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
