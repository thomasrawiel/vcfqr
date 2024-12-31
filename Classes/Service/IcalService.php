<?php
declare(strict_types=1);

namespace TRAW\Vcfqr\Service;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Splitter\ICalendar;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TRAW\Vcfqr\Event\IcalGeneratedEvent;

class IcalService
{
    protected array $tableConf = [];

    public function __construct(private readonly EventDispatcher $eventDispatcher)
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
        if (empty($fullDayField)) {
            $fullDayField = $this->tableConf['fullDay'] ?? null;
        }

        $startDate = (new \DateTime())->setTimestamp($data[$startDateField]);
        $endDate = (new \DateTime())->setTimestamp($data[$endDateField] > 0 ? $data[$endDateField] : ($data[$startDateField]));

        $fullDay = !isset($data[$endDateField]) || $data[$endDateField] === 0 || (isset($data[$fullDayField]) && $data[$fullDayField]);

        if ($fullDay) {
            //if there's no end date or "fullDay" is true, we assume you mean "full day", so format as date and add +1day to enddate
            $endDate->setTimestamp($endDate->getTimestamp() + 86400);
            $startDateString = $startDate->format("Ymd");
            $endDateString = $endDate->format("Ymd");
        }

        $ical = new Vcalendar([
            'VEVENT' => [
                'SUMMARY' => $data[$this->tableConf['summary']],
                'DTSTART' => $startDateString ?? $startDate,
                'DTEND' => $endDateString ?? $endDate,
            ],
        ]);
        $filename = mb_convert_encoding(trim($ical->VEVENT->SUMMARY->__toString()), 'UTF-8', mb_list_encodings());

        $event = new IcalGeneratedEvent($ical, $filename, $data);

        //add your own data to ical
        $this->eventDispatcher->dispatch($event);

        return [
            'filename' => $event->getFilename(),
            'ical' => $event->getIcal()->serialize(),
        ];
    }

    protected function getRecordData($recordUid, $table)
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        return $qb->select(['*'], $table, ['uid' => $recordUid, 'deleted' => 0, 'hidden' => 0], [], [], 1)->fetchAssociative();
    }
}
