<?php
declare(strict_types=1);
defined('TYPO3') or die('Access denied.');

call_user_func(function ($_EXTKEY = 'vcfqr') {
    $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get($_EXTKEY);
    if ($configuration['enableExamples']) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['vcf'] = ['TRAW\Vcfqr\ViewHelpers'];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            "@import 'EXT:vcfqr/Configuration/TSConfig/example.mod.wizards.tsconfig'"
        );
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
                "@import 'EXT:vcfqr/Configuration/TSConfig/ttaddress.mod.wizards.tsconfig'"
            );
        }
    }
    $GLOBALS['TYPO3_CONF_VARS']['EXT'][$_EXTKEY]['addressTableConfiguration'] = 'EXT:vcfqr/Configuration/AddressTableConfiguration.php';
    $GLOBALS['TYPO3_CONF_VARS']['EXT'][$_EXTKEY]['eventTableConfiguration'] = 'EXT:vcfqr/Configuration/EventTableConfiguration.php';
});

