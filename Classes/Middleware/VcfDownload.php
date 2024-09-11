<?php

namespace TRAW\Vcfqr\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TRAW\Vcfqr\Service\VCardService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class VcfDownload
 * @package TRAW\Vcfqr\Middleware
 */
class VcfDownload implements MiddlewareInterface
{
    /**
     * @var ServerRequestInterface|null
     */
    protected ?ServerRequestInterface $request = null;
    /**
     * @var array
     */
    protected array $queryParams = [];

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (false === isset($request->getQueryParams()['tx_vcfqr_address']['uid'])
            || false === isset($request->getQueryParams()['tx_vcfqr_address']['src'])
            || false === MathUtility::canBeInterpretedAsInteger($request->getQueryParams()['tx_vcfqr_address']['uid'])
            || false === MathUtility::canBeInterpretedAsInteger($request->getQueryParams()['tx_vcfqr_address']['src'])
            || false === $this->validateUidsExist($request->getQueryParams()['tx_vcfqr_address']['uid'], $request->getQueryParams()['tx_vcfqr_address']['src'])
            || (int)$request->getQueryParams()['tx_vcfqr_address']['src'] !== (int)($request->getAttribute('routing')->getPageId())

        ) {
            return $handler->handle($request);
        }


        $vcf = $this->fetchVcard($request->getQueryParams()['tx_vcfqr_address']['uid']);

        if (is_null($vcf)) {
            return $handler->handle($request);
        }

        $fileName = $vcf['filename'] . '.vcf';
        header('Content-type: text/vcard; charset=utf-8');
        header('Content-Disposition: inline; filename=' . $fileName);

        print $vcf['vcard'];
        exit;
    }

    /**
     * @param int $addressUid
     * @param int $pageUid
     *
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     */
    protected function validateUidsExist(int $addressUid, int $pageUid): bool
    {
        $cp = GeneralUtility::makeInstance(ConnectionPool::class);
        $pageResult = $cp->getConnectionForTable('pages')->select(['uid'], 'pages', ['uid' => $pageUid, 'hidden' => 0, 'deleted' => 0], [], [], 1)->fetchOne();
        $addressResult = $cp->getConnectionForTable('tt_address')->select(['uid'], 'tt_address', ['uid' => $addressUid, 'hidden' => 0, 'deleted' => 0], [], [], 1)->fetchOne();

        return $pageResult === $pageUid && $addressResult === $addressUid;
    }


    /**
     * @param int $addressUid
     *
     * @return string
     * @throws \Exception
     */
    protected function fetchVcard(int $addressUid): array
    {
        $version = (GeneralUtility::makeInstance(ExtensionConfiguration::class))->get('vcfqr', 'vcardVersion');
        $table = 'tt_address';

        $vcfService = GeneralUtility::makeInstance(VcardService::class);
        $vCard = $vcfService->generateVCardFromRecord($addressUid, $version, $table);

        return $vCard;
    }
}