<?php
declare(strict_types=1);
defined('TYPO3') || die('Access denied.');
call_user_func(function ($_EXTKEY = "vcfqr") {
    $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get($_EXTKEY);
    if ($configuration['enableExamples']) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $_EXTKEY,
            'Configuration/Typoscript/Example/',
            'Vcf-QR-Code Example Content Elements'
        );
    }
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $_EXTKEY,
            'Configuration/Typoscript/TtAddress/',
            'Vcf-QR-Code TtAddress partial'
        );
    }
});
