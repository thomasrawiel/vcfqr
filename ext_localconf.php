<?php
defined('TYPO3') or die('Access denied.');

call_user_func(function ($_EXTKEY = 'vcfqr') {
    $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get($_EXTKEY);
    if ($configuration['enableExamples']) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['vcf'] = ['TRAW\Vcfqr\ViewHelpers'];
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            "@import 'EXT:vcfqr/Configuration/TSConfig/example.mod.wizards.tsconfig"
        );
    }

    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_vcfqr_address[uid]';
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_vcfqr_address[src]';
});

