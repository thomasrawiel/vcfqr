<?php

namespace TRAW\Vcfqr\Service;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Sabre\VObject\Component\VCard;
use TRAW\Vcfqr\Configuration\AddressTableConfiguration;
use TRAW\Vcfqr\Event\VCardGeneratedEvent;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Class VCardService
 * @package TRAW\Vcfqr\Service
 */
class VCardService
{
//    protected const template = 'BEGIN:VCARD
//VERSION:4.0
//N:Mustermann;Erika;;Dr.;
//FN:Dr. Erika Mustermann
//ORG:Wikimedia
//ROLE:Kommunikation
//TITLE:Redaktion & Gestaltung
//PHOTO;MEDIATYPE=image/jpeg:http://commons.wikimedia.org/wiki/File:Erika_Mustermann_2010.jpg
//TEL;TYPE=work,voice;VALUE=uri:tel:+49-221-9999123
//TEL;TYPE=home,voice;VALUE=uri:tel:+49-221-1234567
//ADR;TYPE=home;LABEL="Heidestraße 17\n51147 Köln\nDeutschland":;;Heidestraße 17;Köln;;51147;Germany
//EMAIL:erika@mustermann.de
//REV:20140301T221110Z
//END:VCARD';

    /**
     * @var PhoneNumberUtil
     */
    protected PhoneNumberUtil $phoneUtil;

    /**
     * @var array|string[]
     */
    protected array $tableConf = [];

    public function __construct(private readonly EventDispatcher $eventDispatcher)
    {
        $this->phoneUtil = PhoneNumberUtil::getInstance();

        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['vcfqr']['addressTableConfiguration'])) {
            $this->tableConf = $configurationFile;
        } else {
            $configurationFile = GeneralUtility::getFileAbsFileName($GLOBALS['TYPO3_CONF_VARS']['EXT']['vcfqr']['addressTableConfiguration']);

            if (file_exists($configurationFile)) {
                $this->tableConf = require $configurationFile;
            } else {
                throw new \Exception('Configuration file not found');
            }
        }

        if (empty($this->tableConf)) {
            throw new \Exception('Table configuration empty');
        }
    }

    // @see: https://datatracker.ietf.org/doc/html/rfc6350

    /**
     * @param int    $recordUid
     * @param int    $type
     * @param string $table
     *
     * @return string
     * @throws \Exception
     */
    public function generateVCardFromRecord(int $recordUid, string $table, int $vCardType = VCard::VCARD30): array
    {
        $add = $this->getRecordData($recordUid, $table);
        if (empty($add)) {
            throw new \Exception('Couldn\'t generate vcard from ' . $table . ' because the record does not exist.');
        }
        /** @var \Sabre\VObject\Component\VCard $vcard */
        $vcard = new VCard([
            'N' => [$add[$this->tableConf['lastname']], $add[$this->tableConf['firstname']], $add[$this->tableConf['middlename']], $add[$this->tableConf['title']], $add[$this->tableConf['title_suffix']]],
            'FN' => (!empty($add[$this->tableConf['fullname']]) ? $add[$this->tableConf['fullname']] : ($add[$this->tableConf['title']] . (!empty($add[$this->tableConf['title_suffix']]) ? (' ' . $add[$this->tableConf['title_suffix']]) : '') . ' ' . $add[$this->tableConf['firstname']] . ' ' . $add[$this->tableConf['middlename']] . ' ' . $add[$this->tableConf['lastname']])),
        ]);

        if (!empty($add[$this->tableConf['company']])) {
            $vcard->add('ORG', $add[$this->tableConf['company']]);
        }
        if (!empty($add[$this->tableConf['position']])) {
            $vcard->add('TITLE', $add[$this->tableConf['position']]);
        }
        //photo
        //@todo: add photo to vcard
        if (!empty($add[$this->tableConf['phone']])) {
            $vcard->add('TEL', $this->convertPhoneNumber($add[$this->tableConf['phone']]), ['type' => 'voice']);
        }
        if (!empty($add[$this->tableConf['mobile']])) {
            $vcard->add('TEL', $this->convertPhoneNumber($add[$this->tableConf['mobile']]), ['type' => 'cell']);
        }
        if (!empty($add[$this->tableConf['fax']])) {
            $vcard->add('TEL', $this->convertPhoneNumber($add[$this->tableConf['fax']]), ['type' => 'fax']);
        }
        if (!empty($add[$this->tableConf['address']]) || !empty($add[$this->tableConf['city']]) || !empty($add['zip']) || !empty($add[$this->tableConf['region']]) || !empty($add[$this->tableConf['country']])) {
            $vcard->add('ADR', ['', '', $add[$this->tableConf['address']], $add[$this->tableConf['city']], $add[$this->tableConf['region']], $add[$this->tableConf['zip']], $add[$this->tableConf['country']]], ['type' => 'work']);
        }

        if (!empty($add[$this->tableConf['geo_lat']]) && !empty($add[$this->tableConf['geo_long']])) {
            $vcard->add('GEO', 'geo:' . $add[$this->tableConf['geo_lat']] . ', ' . $add[$this->tableConf['geo_long']]);
        }

        if (!empty($add[$this->tableConf['email']])) {
            $vcard->add('EMAIL', $add[$this->tableConf['email']]);
        }

        if ($vcard->getDocumentType() !== $vCardType) {
            if (in_array($vCardType, [VCard::VCARD21, VCard::VCARD30, VCard::VCARD40])) {
                $vcard = $vcard->convert($vCardType);
            } else {
                $vcard = $vcard->convert(VCard::VCARD30);
            }
        }

        $filename = trim($vcard->FN->__toString());

        $event = new VCardGeneratedEvent($vcard, $filename, $add);
        $this->eventDispatcher->dispatch($event);

        return [
            'filename' => $event->getFilename(),
            'vcard' => $event->getVcard()->serialize(),
        ];
    }

    /**
     * @param string $number
     *
     * @return string
     */
    protected function convertPhoneNumber(string $number): string
    {
        try {
            if (str_starts_with('tel:', $number)) {
                $number = substr($number, 4);
            }
            return $this->phoneUtil->format(
                $this->phoneUtil->parse($number),
                PhoneNumberFormat::INTERNATIONAL
            );
        } catch (NumberParseException $e) {
            return $number;
        }
    }

    /**
     * @param $recordUid
     * @param $table
     *
     * @return false|mixed[]
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getRecordData($recordUid, $table)
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        return $qb->select(['*'], $table, ['uid' => $recordUid, 'deleted' => 0, 'hidden' => 0], [], [], 1)->fetchAssociative();
    }
}
