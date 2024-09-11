<?php

namespace TRAW\Vcfqr\ViewHelpers\Link;

use TRAW\Vcfqr\Service\QRCodeService;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class QRCodeViewHelper
 *
 * Based on uri.typolink, takes the created url and generates a qr code
 *
 * @package TRAW\Vcfqr\ViewHelpers\Link
 */
class QRCodeViewHelper extends AbstractViewHelper
{
    /**
     * @var QRCodeService
     */
    protected QrCodeService $qrCodeService;


    /**
     * @param QRCodeService $qrCodeService
     */
    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('parameter', 'string', 'stdWrap.typolink style parameter string', true);
        $this->registerArgument('fileName', 'string', '', true);
        $this->registerArgument('additionalParams', 'string', 'stdWrap.typolink additionalParams', false, '');
        $this->registerArgument('returnValue', 'string', 'returns either the public url of the image or the image content', false, 'content');
    }

    /**
     * @return string
     */
    public function render()
    {
        $arguments = $this->arguments;

        $parameter = $arguments['parameter'] ?? '';

        $typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
        $typoLinkConfiguration = $typoLinkCodec->decode($parameter);
        $mergedTypoLinkConfiguration = self::mergeTypoLinkConfiguration($typoLinkConfiguration, $arguments);
        $typoLinkParameter = $typoLinkCodec->encode($mergedTypoLinkConfiguration);

        $content = '';
        if ($parameter) {
            $content = self::invokeContentObjectRenderer($arguments, $typoLinkParameter);
        }

        $qrCode = $this->qrCodeService->getQRCode($content, $arguments['fileName'], $fileType = 'svg');
        return $arguments['returnValue'] === 'url' ? $qrCode->getPublicUrl() : $qrCode->getContents();
    }

    /**
     * @param array  $arguments
     * @param string $typoLinkParameter
     *
     * @return string
     */
    protected static function invokeContentObjectRenderer(array $arguments, string $typoLinkParameter): string
    {
        $instructions = [
            'parameter' => $typoLinkParameter,
            'forceAbsoluteUrl' => 1,
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

        return $typoLinkConfiguration;
    }
}