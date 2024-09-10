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
    ],
];
