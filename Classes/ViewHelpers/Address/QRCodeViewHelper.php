<?php

namespace TRAW\Vcfqr\ViewHelpers\Address;

use TRAW\Vcfqr\Service\QRCodeService;
use TRAW\Vcfqr\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Class QRCodeViewHelper
 *
 * Based on uri.typolink, takes the created url and generates a qr code
 *
 * @package TRAW\Vcfqr\ViewHelpers\Link
 */
class QRCodeViewHelper extends AbstractTagBasedViewHelper
{
    protected QrCodeService $qrCodeService;

    /**
     * @var string
     */
    protected $tagName = 'img';


    public function __construct(QRCodeService $qrCodeService)
    {
        parent::__construct();
        $this->qrCodeService = $qrCodeService;
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('address', 'int', 'address uid', true);
        $this->registerArgument('fileName', 'string', 'filename of the qrcode image');
    }

    public function render()
    {

        $parameter = $this->renderingContext->getRequest()->getAttribute('routing')->getPageId();
        $arguments = $this->arguments;
        $arguments['address_src'] = $parameter;

        $typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
        $typoLinkConfiguration = $typoLinkCodec->decode($parameter);
        $mergedTypoLinkConfiguration = self::mergeTypoLinkConfiguration($typoLinkConfiguration, $arguments);
        $typoLinkParameter = $typoLinkCodec->encode($mergedTypoLinkConfiguration);

        $content = '';
        if ($parameter) {
            $content = self::invokeContentObjectRenderer($arguments, $typoLinkParameter);
        }

        $qrCode = $this->qrCodeService->getQRCode($content, !empty($arguments['fileName'])?$arguments['fileName']:($arguments['address_src'] . '_' . $arguments['address_src']), $fileType = 'svg');
        new Tag

        $this->tag->addAttribute('src', $qrCode->getPublicUrl());
        $this->tag->addAttribute('width', 500);
        $this->tag->addAttribute('height', 500);
        $this->tag->addAttribute('alt', '');

        return $this->tag->render();
    }

    protected static function invokeContentObjectRenderer(array $arguments, string $typoLinkParameter): string
    {
        $instructions = [
            'parameter' => $typoLinkParameter,
            'forceAbsoluteUrl' => true,
        ];

        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        return $contentObject->createUrl($instructions);
    }

    /**
     * Merges view helper arguments with typolink parts.
     */
    protected static function mergeTypoLinkConfiguration(array $typoLinkConfiguration, array $arguments): array
    {
        if ($typoLinkConfiguration === []) {
            return $typoLinkConfiguration;
        }

        $additionalParameters = $arguments['additionalParams'] ?? '';

        // Combine additionalParams
        if ($additionalParameters) {
            $typoLinkConfiguration['additionalParams'] .= $additionalParameters;
        }

        $typoLinkConfiguration['additionalParams'] = self::mergeWithMiddlewareParams($typoLinkConfiguration['additionalParams'], $arguments);

        return $typoLinkConfiguration;
    }

    protected static function mergeWithMiddlewareParams($additionalParams, $arguments): string
    {
        return $additionalParams . ConfigurationUtility::getDownloadParameters($arguments['address'], $arguments['address_src']);
    }
}