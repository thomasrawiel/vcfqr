<?php
declare(strict_types=1);
defined('TYPO3') or die('Access denied.');

call_user_func(function ($_EXTKEY = 'vcfqr', $table = 'tt_address') {
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, [
            'hideqrcode' => [
                'label' => 'LLL:EXT:vcfqr/Resources/Private/Language/locallang_be.xlf:tt_address.hideqrcode',
                'config' => [
                    'type' => 'check',
                    'renderType' => 'checkboxToggle',
                    'items' => [
                        ['label' => '', 'invertStateDisplay' => true,],
                    ],
                    'default' => 1,
                ],
            ],
        ]);
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette($table, 'paletteHidden', 'hideqrcode');
    }
});