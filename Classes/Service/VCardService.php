<?php

namespace TRAW\Vcfqr\Service;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Sabre\VObject\Component\VCard;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     *
     */
    public function __construct()
    {
        $this->phoneUtil = PhoneNumberUtil::getInstance();
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
    public function generateVCardFromRecord(int $recordUid, int $vCardType = VCard::VCARD30, string $table = 'tt_address'): array
    {
        if (!ExtensionManagementUtility::isLoaded('tt_address')) {
            throw new \Exception('Couldn\'t generate vcard from tt_address because the extension is not installed.');
        }

        $add = $this->getRecordData($recordUid, $table);
        if (empty($add)) {
            throw new \Exception('Couldn\'t generate vcard from tt_address because the record does not exist.');
        }
        /** @var \Sabre\VObject\Component\VCard $vcard */
        $vcard = new VCard([
            'N' => [$add['last_name'], $add['first_name'], $add['middle_name'], $add['title'], $add['title_suffix']],
            'FN' => (!empty($add['name']) ? $add['name'] : ($add['title'] . (!empty($add['title_suffix']) ? (' ' . $add['title_suffix']) : '') . ' ' . $add['first_name'] . ' ' . $add['middle_name'] . ' ' . $add['last_name'])),
        ]);

        if (!empty($add['company'])) {
            $vcard->add('ORG', $add['company']);
        }
        if (!empty($add['position'])) {
            $vcard->add('TITLE', $add['position']);
        }
        //photo
        //@todo: add photo to vcard
        if (!empty($add['phone'])) {
            $vcard->add('TEL', $this->convertPhoneNumber($add['phone']), ['type' => 'voice']);
        }
        if (!empty($add['mobile'])) {
            $vcard->add('TEL', $this->convertPhoneNumber($add['mobile']), ['type' => 'cell']);
        }
        if (!empty($add['fax'])) {
            $vcard->add('TEL', $this->convertPhoneNumber($add['fax']), ['type' => 'fax']);
        }
        if (!empty($add['address']) || !empty($add['city']) || !empty($add['zip']) || !empty($add['region']) || !empty($add['country'])) {
            $vcard->add('ADR', ['', '', $add['address'], $add['city'], $add['region'], $add['zip'], $add['country']], ['type' => 'work']);
        }

        if (!empty($add['latitude']) && !empty($add['longitude'])) {
            $vcard->add('GEO', 'geo:' . $add['latitude'] . ', ' . $add['longitude']);
        }

        if (!empty($add['email'])) {
            $vcard->add('EMAIL', $add['email']);
        }

        if ($vcard->getDocumentType() !== $vCardType) {
            if(in_array($vCardType, [VCard::VCARD21, VCard::VCARD30, VCard::VCARD40])){
                $vcard = $vcard->convert($vCardType);
            }else {
                $vcard = $vcard->convert(VCard::VCARD30);
            }
        }

        return [

            'filename' => $vcard->FN->__toString(),
            'vcard' => $vcard->serialize(),
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
    protected function getRecordData($recordUid, $table = 'tt_address')
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        return $qb->select(['*'], $table, ['uid' => $recordUid, 'deleted' => 0, 'hidden' => 0], [], [], 1)->fetchAssociative();
    }
}