<?php

namespace TRAW\Vcfqr\Service;

use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use TRAW\Vcfqr\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\StorageRepository;

/**
 * Class QRCodeService
 * @package TRAW\Vcfqr\Service
 */
class QRCodeService
{

    /**
     * @param StorageRepository    $storageRepository
     * @param ConfigurationUtility $configurationUtility
     */
    public function __construct(
        private readonly StorageRepository    $storageRepository,
        private readonly ConfigurationUtility $configurationUtility
    )
    {
    }

    /**
     * @param string $data
     * @param string $filename
     *
     * @return \TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\FileInterface|\TYPO3\CMS\Core\Resource\ProcessedFile|null
     */
    public function getQRCode(string $data, string $filename)
    {
        $filename = $filename . '.svg';
        $absoluteFileName = Environment::getPublicPath() . $this->configurationUtility->getFolder()->getPublicUrl() . $filename;
        $readableFileName = $this->configurationUtility->getFolder()->getReadablePath() . $filename;

        if (file_exists($absoluteFileName)) {
            $file = $this->configurationUtility->getStorage()->getFile($readableFileName);
        } else {
            $file = $this->createQRCode($data, $filename);
        }

        if ($file->exists() && ((time() - (int)$file->getProperty('tstamp')) <= $this->configurationUtility->getQRCodeCacheLifetime())) {
            return $file;
        } else {
            // if file is older than cache life time, recreate file
            return $this->createQRCode($data, $filename);
        }
    }

    /**
     * @param string $data
     * @param string $filename
     *
     * @return File|FileInterface|ProcessedFile|null
     * @throws \TYPO3\CMS\Core\Resource\Exception\ExistingTargetFileNameException
     */
    protected function createQRCode(string $data, string $filename): File|FileInterface|ProcessedFile|null
    {
        $options = [
            'outputInterface' => QRMarkupSVG::class,
            'version' => Version::AUTO,
            'outputBase64' => false,
            'connectPaths' => true,
//            'keepAsSquare' => [
//                QRMatrix::M_FINDER_DARK,
//                QRMatrix::M_FINDER_DOT,
//                QRMatrix::M_ALIGNMENT_DARK,
//            ],
        ];


        $out = (new QRCode(new QROptions($options)))->render($data);
        $tmpFile = '/tmp/' . $filename;

        file_put_contents($tmpFile, $out);

        return $this->configurationUtility->getStorage()->addFile(
            $tmpFile,
            $this->configurationUtility->getFolder(),
            $filename,
            DuplicationBehavior::REPLACE
        );
    }
}