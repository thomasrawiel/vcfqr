<?php

namespace TRAW\Vcfqr\ViewHelpers\Link;

use TRAW\Vcfqr\Service\QRCodeService;
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
        $this->registerArgument('parameter', 'string', 'stdWrap.typolink style parameter string', true);
        $this->registerArgument('fileName', 'string', '', true);
        $this->registerArgument('additionalParams', 'string', 'stdWrap.typolink additionalParams', false, '');
        $this->registerArgument('language', 'string', 'link to a specific language - defaults to the current language, use a language ID or "current" to enforce a specific language', false);
        $this->registerArgument('addQueryString', 'string', 'If set, the current query parameters will be kept in the URL. If set to "untrusted", then ALL query parameters will be added. Be aware, that this might lead to problems when the generated link is cached.', false, false);
        $this->registerArgument('addQueryStringExclude', 'string', 'Define parameters to be excluded from the query string (only active if addQueryString is set)', false, '');
        $this->registerArgument('absolute', 'bool', 'Ensure the resulting URL is an absolute URL', false, false);
    }

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
        $this->tag->addAttribute('src', $qrCode->getPublicUrl());
        $this->tag->addAttribute('width', 500);
        $this->tag->addAttribute('height', 500);
        $this->tag->addAttribute('alt', '');

        return $this->tag->render();
    }

    protected static function invokeContentObjectRenderer(array $arguments, string $typoLinkParameter): string
    {
        $addQueryString = $arguments['addQueryString'] ?? false;
        $addQueryStringExclude = $arguments['addQueryStringExclude'] ?? '';
        $absolute = $arguments['absolute'] ?? false;

        $instructions = [
            'parameter' => $typoLinkParameter,
            'forceAbsoluteUrl' => $absolute,
        ];
        if (isset($arguments['language']) && $arguments['language'] !== null) {
            $instructions['language'] = (string)$arguments['language'];
        }
        if ($addQueryString && $addQueryString !== 'false') {
            $instructions['addQueryString'] = $addQueryString;
            $instructions['addQueryString.'] = [
                'exclude' => $addQueryStringExclude,
            ];
        }

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