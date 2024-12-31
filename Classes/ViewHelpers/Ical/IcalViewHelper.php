<?php
declare(strict_types=1);

namespace TRAW\Vcfqr\ViewHelpers\Ical;

use TRAW\Vcfqr\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class IcalViewHelper extends AbstractViewHelper {
   public function initializeArguments() {
       $this->registerArgument('record', 'int', 'record uid', true);
       $this->registerArgument('startField', 'string', 'The fieldname that holds the begin date', false);
       $this->registerArgument('endField', 'string', 'The fieldname that holds the end date', false);
       $this->registerArgument('fullDayField', 'string', 'The fieldname that holds the boolean if it\'s a full day event', false);
       $this->registerArgument('target', 'string', 'Define where to display the linked URL', false, '_blank');
       $this->registerArgument('class', 'string', 'Define classes for the link element', false, '');
       $this->registerArgument('title', 'string', 'Define the title for the link element', false, '');
       $this->registerArgument('additionalAttributes', 'array', 'Additional tag attributes to be added directly to the resulting HTML tag', false, []);
       $this->registerArgument('textWrap', 'string', 'Wrap the link using the typoscript "wrap" data type', false, '');
   }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        //link to current site
        $parameter = (string)$renderingContext->getRequest()->getAttribute('routing')->getPageId();

        $typoLinkCodec = GeneralUtility::makeInstance(TypoLinkCodecService::class);
        $typoLinkConfiguration = $typoLinkCodec->decode($parameter);

        $arguments['record_src'] = $parameter;
        // Merge the $parameter with other arguments
        $mergedTypoLinkConfiguration = self::mergeTypoLinkConfiguration($typoLinkConfiguration, $arguments);
        $typoLinkParameter = $typoLinkCodec->encode($mergedTypoLinkConfiguration);

        $content = (string)$renderChildrenClosure();

        if ($parameter) {
            $content = self::invokeContentObjectRenderer($arguments, $typoLinkParameter, $content);
        }
        return $content;
    }

    /**
     * @param array  $arguments
     * @param string $typoLinkParameter
     * @param string $content
     *
     * @return string
     */
    protected static function invokeContentObjectRenderer(array $arguments, string $typoLinkParameter, string $content): string
    {
        $aTagParams = self::serializeTagParameters($arguments);

        $instructions = [
            'parameter' => $typoLinkParameter,
            'ATagParams' => $aTagParams,
            'forceAbsoluteUrl' => 1,
        ];

        if ((string)($arguments['textWrap'] ?? '') !== '') {
            $instructions['ATagBeforeWrap'] = true;
            $instructions['wrap'] = $arguments['textWrap'] ?? '';
        }

        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        return $contentObject->typoLink($content, $instructions);
    }

    /**
     * @param array $arguments
     *
     * @return string
     */
    protected static function serializeTagParameters(array $arguments): string
    {
        // array(param1 -> value1, param2 -> value2) --> param1="value1" param2="value2" for typolink.ATagParams
        $extraAttributes = [];
        $additionalAttributes = $arguments['additionalAttributes'] ?? [];
        foreach ($additionalAttributes as $attributeName => $attributeValue) {
            $extraAttributes[] = $attributeName . '="' . htmlspecialchars((string)$attributeValue) . '"';
        }
        return implode(' ', $extraAttributes);
    }

    /**
     * Merges view helper arguments with typolink parts.
     */
    protected static function mergeTypoLinkConfiguration(array $typoLinkConfiguration, array $arguments): array
    {
        if ($typoLinkConfiguration === []) {
            return $typoLinkConfiguration;
        }

        $target = $arguments['target'] ?? '';
        $class = $arguments['class'] ?? '';
        $title = $arguments['title'] ?? '';
        $additionalParams = $arguments['additionalParams'] ?? '';

        // Override target if given in target argument
        if ($target) {
            $typoLinkConfiguration['target'] = $target;
        }
        // Combine classes if given in both "parameter" string and "class" argument
        if ($class) {
            $classes = explode(' ', trim($typoLinkConfiguration['class']) . ' ' . trim($class));
            $typoLinkConfiguration['class'] = implode(' ', array_unique(array_filter($classes)));
        }
        // Override title if given in title argument
        if ($title) {
            $typoLinkConfiguration['title'] = $title;
        }
        // Combine additionalParams
        if ($additionalParams) {
            $typoLinkConfiguration['additionalParams'] .= $additionalParams;
        }

        $typoLinkConfiguration['additionalParams'] = self::mergeWithMiddlewareParams($typoLinkConfiguration['additionalParams'], $arguments);

        return $typoLinkConfiguration;
    }

    /**
     * @param $additionalParams
     * @param $arguments
     *
     * @return string
     */
    protected static function mergeWithMiddlewareParams($additionalParams, $arguments): string
    {
        return $additionalParams . ConfigurationUtility::getIcalDownloadParameters((int)$arguments['record'], (int)$arguments['record_src']);
    }
}
