<?php

namespace TRAW\Vcfqr\Service;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Splitter\ICalendar;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IcalService
{
    protected array $tableConf = [];

    public function __construct()
    {
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['vcfqr']['eventTableConfiguration'])) {
            $this->tableConf = $configurationFile;
        } else {
            $configurationFile = GeneralUtility::getFileAbsFileName($GLOBALS['TYPO3_CONF_VARS']['EXT']['vcfqr']['eventTableConfiguration']);

            if (file_exists($configurationFile)) {
                $this->tableConf = require $configurationFile;
            } else {
                throw new \Exception('Configuration file not found');
            }
        }
    }

    public function generateIcalFromRecord(int $recordUid, string $table, ?string $startDateField = null, ?string $endDateField = null, ?string $fullDayField = null): array
    {
        $record = $this->getRecordData($recordUid, $table);

        return $this->generateIcalFromData($record, $startDateField, $endDateField, $fullDayField);
    }

    public function generateIcalFromData($data, ?string $startDateField = null, ?string $endDateField = null, ?string $fullDayField = null): array
    {

        if (empty($startDateField)) {
            $startDateField = $this->tableConf['startDate'];
        }
        if (empty($endDateField)) {
            $endDateField = $this->tableConf['endDate'];
        }

        $startDate = (new \DateTime())->setTimestamp($data[$startDateField]);
        $endDate = (new \DateTime())->setTimestamp($data[$endDateField]);

        $ical = new Vcalendar([
            'VEVENT' => [
                'SUMMARY' => $data[$this->tableConf['summary']],
                'DTSTART' => $startDate,
                'DTEND' => $endDate,
            ],
        ]);

        return [
            'filename' => mb_convert_encoding(trim($ical->VEVENT->SUMMARY->__toString()), 'UTF-8', mb_list_encodings()),
            'ical' => $ical->serialize(),
        ];
    }

    protected function getRecordData($recordUid, $table)
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        return $qb->select(['*'], $table, ['uid' => $recordUid, 'deleted' => 0, 'hidden' => 0], [], [], 1)->fetchAssociative();
    }
}
