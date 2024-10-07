<?php

namespace TRAW\Vcfqr\Event;

use Sabre\VObject\Component\VCalendar;

/**
 * Class IcalGeneratedEvent
 */
final class IcalGeneratedEvent
{
    /**
     * @var VCalendar|null
     */
    private ?VCalendar $ical = null;
    /**
     * @var string
     */
    private string $filename = '';
    /**
     * @var array
     */
    private array $originalData = [];

    /**
     * @param VCalendar $ical
     * @param string    $filename
     * @param array     $originalData
     */
    public function __construct(VCalendar $ical, string $filename, array $originalData)
    {
        $this->ical = $ical;
        $this->filename = $filename;
        $this->originalData = $originalData;
    }

    /**
     * @return VCalendar
     */
    public function getIcal(): VCalendar
    {
        return $this->ical;
    }

    /**
     * @param VCalendar $ical
     *
     * @return void
     */
    public function setIcal(VCalendar $ical): void
    {
        $this->ical = $ical;
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
}