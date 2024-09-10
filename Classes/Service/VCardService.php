<?php

namespace TRAW\Vcfqr\Service;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class VCardService
{
    protected const template = 'BEGIN:VCARD
VERSION:4.0
N:Mustermann;Erika;;Dr.;
FN:Dr. Erika Mustermann
ORG:Wikimedia
ROLE:Kommunikation
TITLE:Redaktion & Gestaltung
PHOTO;MEDIATYPE=image/jpeg:http://commons.wikimedia.org/wiki/File:Erika_Mustermann_2010.jpg
TEL;TYPE=work,voice;VALUE=uri:tel:+49-221-9999123
TEL;TYPE=home,voice;VALUE=uri:tel:+49-221-1234567
ADR;TYPE=home;LABEL="Heidestraße 17\n51147 Köln\nDeutschland":;;Heidestraße 17;Köln;;51147;Germany
EMAIL:erika@mustermann.de
REV:20140301T221110Z
END:VCARD';

    protected PhoneNumberUtil $phoneUtil;

    public function __construct()
    {
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

// @see: https://datatracker.ietf.org/doc/html/rfc6350
    public function generateVCardFromRecord($recordUid, $table = 'tt_address')
    {
        if (!ExtensionManagementUtility::isLoaded('tt_address')) {
            throw new \Exception('Couldn\'t generate vcard from tt_address because the extension is not installed.');
        }

        $address = $this->getRecordData($recordUid, $table);
        if (empty($address)) {
            throw new \Exception('Couldn\'t generate vcard from tt_address because the record does not exist.');
        }

        $sep = ';';
        $eol = "\r\n";

        $vcard = 'BEGIN:VCARD' . $eol . 'VERSION:4.0' . $eol;
        //name parts
        $vcard .= 'N:' . $address['last_name'] . $sep . $address['first_name'] . $sep . $address['middle_name'] . $sep . $address['title'] . ' ' . $address['title_suffix'] . $eol;
        //full name
        $vcard .= 'FN:' . (!empty($address['name']) ? $address['name'] : ($address['title'] . ' ' . $address['title_suffix'] . ' ' . $address['first_name'] . ' ' . $address['middle_name'] . ' ' . $address['last_name'])) . $eol;
        //organisation/company
        if (!empty($address['company'])) {
            $vcard .= 'ORG:' . $address['company'] . $eol;
        }
        if (!empty($address['position'])) {
            $vcard .= 'ROLE:' . $address['position'] . $eol;
        }
        //photo

        //tel
        if (!empty($address['phone'])) {
            $vcard .= 'TEL;VALUE=uri;TYPE="voice,work":tel:' . $this->convertPhoneNumber($address['phone']) . $eol;
        }
        if (!empty($address['mobile'])) {
            $vcard .= 'TEL;VALUE=uri;TYPE="cell,work":tel:' . $this->convertPhoneNumber($address['mobile']) . $eol;
        }
        if (!empty($address['fax'])) {
            $vcard .= 'TEL;VALUE=uri;TYPE="fax,work":tel:' . $this->convertPhoneNumber($address['fax']) . $eol;
        }
        //address

        //email
        if (!empty($address['email'])) {

        }


    }

    protected function convertPhoneNumber(string $number): string
    {
        try {
            if (str_starts_with('tel:', $number)) {
                $number = substr($number, 4);
            }
            return $this->phoneUtil->format(
                $this->phoneUtil->parse($number),
                PhoneNumberFormat::RFC3966
            );
        } catch (NumberParseException $e) {
            return $number;
        }
    }

    protected function getRecordData($recordUid, $table = 'tt_address')
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        return $qb->select(['*'], $table, ['uid' => $recordUid, 'deleted' => 0, 'hidden' => 0], [], [], 1)->fetchAssociative();
    }
}