<?php
return [
    'frontend' => [
        'TRAW\Vcfqr\VcfDownload' => [
            'target' => \TRAW\Vcfqr\Middleware\VcfDownload::class,
            'after' => [
                'typo3/cms-frontend/page-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/tsfe',
            ],
        ],
        'TRAW\Vcfqr\IcalDownload' => [
            'target' => \TRAW\Vcfqr\Middleware\IcalDownload::class,
            'after' => [
                'typo3/cms-frontend/page-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/tsfe',
            ],
        ],
    ],
];
