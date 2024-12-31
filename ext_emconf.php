<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'VCF-QR',
    'description' => 'Create QR-Code with VCF card',
    'category' => 'misc',
    'author' => 'Thomas Rawiel',
    'author_email' => 'thomas.rawiel@gmail.com',
    'state' => 'beta',
    'clearCacheOnLoad' => 0,
    'version' => '0.4.2',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.9.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'tt_address' => '8.0.0-9.99.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'TRAW\\Vcfqr\\' => 'Classes/',
            'chillerlan\\Settings\\' => 'vendor/chillerlan/php-settings-container/src',
            'chillerlan\\QRCode' => 'vendor/chillerlan/php-qrcode/src',
            'libphonenumber\\' => 'vendor/giggsley/libphonenumber-for-php/src/',
            'Giggsey\\Locale\\' => 'vendor/giggsley/locale/src/',
            'Sabre\\Uri\\' => 'vendor/sabre/uri/lib/',
            'Sabre\\VObject\\' => 'vendor/sabre/vobject/lib/',
            'Sabre\\Xml\\' => 'vendor/sabre/xml/lib/',
        ],
    ],
];
