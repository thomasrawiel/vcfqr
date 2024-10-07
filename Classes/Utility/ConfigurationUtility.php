<?php

namespace TRAW\Vcfqr\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\InaccessibleFolder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConfigurationUtility
 */
class ConfigurationUtility
{
    /**
     * @var array|mixed|null
     */
    protected ?array $configuration = null;
    /**
     * @var ResourceStorage|null
     */
    protected ResourceStorage $storage;
    /**
     * @var Folder|InaccessibleFolder|null
     */
    protected null|Folder|InaccessibleFolder $folder = null;

    /**
     * @param StorageRepository $storageRepository
     *
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     * @throws \TYPO3\CMS\Core\Resource\Exception\ExistingTargetFolderException
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderWritePermissionsException
     */
    public function __construct(private readonly StorageRepository $storageRepository)
    {
        $this->configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('vcfqr');
        $this->storage = ($this->configuration['storageUid'] ?? false)
            ? $this->storageRepository->getStorageObject($this->configuration['storageUid'])
            : $this->storageRepository->getDefaultStorage();
        $this->folder = $this->storage->hasFolder($this->configuration['qrfolder'])
            ? $this->storage->getFolder($this->configuration['qrfolder'])
            : $this->storage->createFolder($this->configuration['qrfolder']);
    }

    /**
     * @return array|null
     */
    public function getConfiguration(): ?array
    {
        return $this->configuration;
    }

    /**
     * @return ResourceStorage
     */
    public function getStorage(): ResourceStorage
    {
        return $this->storage;
    }

    /**
     * @return Folder|InaccessibleFolder|null
     */
    public function getFolder(): Folder|InaccessibleFolder|null
    {
        return $this->folder;
    }

    /**
     * @return int
     */
    public function getQRCodeCacheLifetime(): int
    {
        return $this->configuration['qrcodeCacheLifetime'] ?? 0;
    }

    /**
     * @param int $addressUid
     * @param int $pageUid
     *
     * @return string
     */
    public static function getVcfDownloadParameters(int $addressUid, int $pageUid): string
    {
        return sprintf('&tx_vcfqr_address[uid]=%d&tx_vcfqr_address[src]=%d', $addressUid, $pageUid);
    }

    /**
     * @param int $record
     * @param int $pageUid
     *
     * @return string
     */
    public static function getIcalDownloadParameters(int $recordUid, int $pageUid): string
    {
        return sprintf('&tx_vcfqr_ical[uid]=%d&tx_vcfqr_ical[src]=%d', $recordUid, $pageUid);
    }

    /**
     * @return string
     */
    public function getAddressTablename(): string
    {
        return $this->configuration['addressTablename'] ?? '';
    }
}
