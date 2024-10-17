<?php
declare(strict_types=1);

namespace TRAW\Vcfqr\Event;

use chillerlan\QRCode\QRCode;

/**
 * Class QrCodeGeneratedEvent
 */
final class QrCodeGeneratedEvent
{
    /**
     * @var QRCode|null
     */
    private ?QRCode $qrcode = null;
    /**
     * @var string
     */
    private string $filename = '';
    /**
     * @var array
     */
    private string $originalData = '';

    /**
     * @param QRCode $qrcode
     * @param string $filename
     * @param array  $originalData
     */
    public function __construct(QRCode $qrcode, string $filename, string $originalData)
    {
        $this->qrcode = $qrcode;
        $this->filename = $filename;
        $this->originalData = $originalData;
    }

    /**
     * @return QRCode|null
     */
    public function getQrcode(): ?QRCode
    {
        return $this->qrcode;
    }

    /**
     * @param QRCode|null $qrcode
     *
     * @return void
     */
    public function setQrcode(?QRCode $qrcode): void
    {
        $this->qrcode = $qrcode;
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
    public function getOriginalData(): string
    {
        return $this->originalData;
    }
}