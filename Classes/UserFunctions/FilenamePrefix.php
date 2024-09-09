<?php

namespace TRAW\Vcfqr\UserFunctions;

use TRAW\Vcfqr\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FilenamePrefix
{
    protected ConfigurationUtility $configurationUtility;

    public function __construct()
    {
        $this->configurationUtility = GeneralUtility::makeInstance(ConfigurationUtility::class);
    }

    public function getPrefix($parameters)
    {
        return $this->configurationUtility->getFolder()->getPublicUrl();
    }
}