<?php
declare(strict_types=1);

namespace TRAW\Vcfqr\Event;

/**
 * Class BeforeQrCodeGeneratedEvent
 */
final class BeforeQrCodeGeneratedEvent
{
    /**
     * @var array
     */
    private array $options = [];

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}