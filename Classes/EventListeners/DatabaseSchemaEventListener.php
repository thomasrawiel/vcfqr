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
class DatabaseSchemaEventListener
{
    public function __invoke(AlterTableDefinitionStatementsEvent $event): void
    {
        if (GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('vcfqr', 'enableExamples')) {
            $tt_content = (string)file_get_contents(
                ExtensionManagementUtility::extPath('vcfqr') .
                'Resources/Private/Sql/tt_content_example.sql'
            );
            $event->addSqlData($tt_content);
        }
    }
}
