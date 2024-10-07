<?php

namespace TRAW\Vcfqr\EventListeners;

use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 *
 * Class DatabaseSchemaEventListener
 */
final class DatabaseSchemaEventListener
{
    public function performNecessarySchemaUpdate(AlterTableDefinitionStatementsEvent $event): void
    {
        $ext = 'vcfqr';
        $extPath = ExtensionManagementUtility::extPath($ext);

        if ((bool)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get($ext, 'enableExamples')) {
            $tt_content = (string)file_get_contents($extPath . 'Resources/Private/Sql/tt_content_example.sql');
            $event->addSqlData($tt_content);
        }

        if (ExtensionManagementUtility::isLoaded('tt_address')) {
            $tt_address = (string)file_get_contents($extPath . 'Resources/Private/Sql/tt_address.sql');
            $event->addSqlData($tt_address);
        }
    }
}
