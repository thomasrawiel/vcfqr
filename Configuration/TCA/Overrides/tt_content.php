<?php
defined('TYPO3') or die ('Access denied.');

call_user_func(function ($_EXTKEY = 'vcfqr', $table = 'tt_content') {
    $configuration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get($_EXTKEY);
    if ($configuration['enableExamples']) {
        $LLL = 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be.xlf:';

        $cTypes = ['link'];

        $columnsForExampleCTypes = [
            'tx_vcfqr_filename' => [
                'label' => $LLL . 'tx_vcfqr_filename',
                'description' => $LLL . 'tx_vcfqr_filename.description',
                'config' => [
                    'type' => 'slug',
                    'size' => 50,
                    'generatorOptions' => [
                        'fields' => [['header', 'uid'], 'uid', 'pid'],
                        'fieldSeparator' => '-',
                        'prefixParentPageSlug' => false,
                    ],
                    'appearance' => [
                        'prefix' => \TRAW\Vcfqr\UserFunctions\FilenamePrefix::class . '->getPrefix',
                    ],
                    'fallbackCharacter' => '-',
                    'eval' => 'unique',
                    'max' => 50,
                    'default' => '',
                ],
            ],
        ];

        $GLOBALS['TCA'][$table]['palettes'][$_EXTKEY . '_link'] = ['showitem' => 'header,--linebreak--,header_link,--linebreak--,tx_vcfqr_filename'];

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
            $columnsForExampleCTypes['tx_vcfqr_address'] = [
                'label' => $LLL . 'tx_vcfqr_address',
                'description' => $LLL . 'tx_vcfqr_address.description',
                'config' => [
                    'type' => 'group',
                    'allowed' => 'tt_address',
                    'size' => 1,
                ],
            ];

            $cTypes = array_merge($cTypes, ['vcf', 'vcf_qr']);
            $GLOBALS['TCA'][$table]['palettes'][$_EXTKEY . '_vcf'] = ['showitem' => 'header,--linebreak--,tx_vcfqr_address,--linebreak--,tx_vcfqr_filename'];
            $GLOBALS['TCA'][$table]['palettes'][$_EXTKEY . '_vcf_qr'] = ['showitem' => 'header,--linebreak--,tx_vcfqr_address,--linebreak--,tx_vcfqr_filename'];

        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $columnsForExampleCTypes);
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItemGroup($table, 'CType', $_EXTKEY, $LLL . 'CTypeGroup');

        foreach ($cTypes as $type) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
                $table,
                'CType',
                [
                    'label' => $LLL . 'CType_' . $type,
                    'value' => $_EXTKEY . '_' . $type,
                    'icon' => 'tx-vcfqr-qr-icon',
                    'group' => $_EXTKEY,
                ],
            );
            $GLOBALS['TCA'][$table]['ctrl']['typeicon_classes'][$_EXTKEY . '_' . $type] = 'tx-vcfqr-qr-icon';
            $GLOBALS['TCA'][$table]['types'][$_EXTKEY . '_' . $type]['showitem'] = '
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                    --palette--;;general,
                    --palette--;;vcfqr_' . $type . ',
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,
                    --palette--;;frames,
                    --palette--;;appearanceLinks,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    --palette--;;hidden,
                    --palette--;;access,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                    categories,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                    rowDescription,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
            ';
        }
    }
});

