<?php

namespace TRAW\Vcfqr\Event;

use Sabre\VObject\Component\VCard;


/**
 * Class VCardGeneratedEvent
 */
final class VCardGeneratedEvent
{
    /**
     * @var VCard|null
     */
    private ?VCard $vcard = null;
    /**
     * @var string
     */
    private string $filename = '';
    /**
     * @var array
     */
    private array $originalData = [];

    /**
     * @param VCard  $vcard
     * @param string $filename
     * @param array  $originalData
     */
    public function __construct(VCard $vcard, string $filename, array $originalData)
    {
        $this->vcard = $vcard;
        $this->filename = $filename;
        $this->originalData = $originalData;
    }

    /**
     * @return VCard|null
     */
    public function getVcard(): ?VCard
    {
        return $this->vcard;
    }

    /**
     * @param VCard|null $vcard
     *
     * @return void
     */
    public function setVcard(?VCard $vcard): void
    {
        $this->vcard = $vcard;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     *
     * @return void
     */
    public function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * @return array
     */
    public function getOriginalData(): array
    {
        return $this->originalData;
    }

    /**
     * @param array $originalData
     *
     * @return void
     */
    public function setOriginalData(array $originalData): void
    {
        $this->originalData = $originalData;
    }
}