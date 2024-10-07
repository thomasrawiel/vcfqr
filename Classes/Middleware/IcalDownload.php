<?php

namespace TRAW\Vcfqr\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TRAW\Vcfqr\Service\IcalService;
use TRAW\Vcfqr\Service\VCardService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class IcalDownload
 */
class IcalDownload implements MiddlewareInterface
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
     * @var array
     */
    protected array $configuration = [];

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->configuration = (GeneralUtility::makeInstance(ExtensionConfiguration::class))->get('vcfqr');

        if (false === isset($request->getQueryParams()['tx_vcfqr_ical']['uid'])
            || false === isset($request->getQueryParams()['tx_vcfqr_ical']['src'])
            || false === MathUtility::canBeInterpretedAsInteger($request->getQueryParams()['tx_vcfqr_ical']['uid'])
            || false === MathUtility::canBeInterpretedAsInteger($request->getQueryParams()['tx_vcfqr_ical']['src'])
            || false === $this->validateUidsExist($request->getQueryParams()['tx_vcfqr_ical']['uid'], $request->getQueryParams()['tx_vcfqr_ical']['src'])
            || (int)$request->getQueryParams()['tx_vcfqr_ical']['src'] !== (int)($request->getAttribute('routing')->getPageId())

        ) {
            return $handler->handle($request);
        }

        $ical = $this->fetchIcal($request->getQueryParams()['tx_vcfqr_ical']['uid']);

        if (is_null($ical)) {
            return $handler->handle($request);
        }

        $fileName = $ical['filename'] . '.ics';
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename=' . $fileName);

        print $ical['ical'];
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
        $addressResult = $cp->getConnectionForTable($this->configuration['eventTablename'])->select(['uid'], $this->configuration['eventTablename'], ['uid' => $addressUid, 'hidden' => 0, 'deleted' => 0], [], [], 1)->fetchOne();

        return $pageResult === $pageUid && $addressResult === $addressUid;
    }


    /**
     * @param int $addressUid
     *
     * @return string
     * @throws \Exception
     */
    protected function fetchIcal(int $recordUid): array
    {
        $version = $this->configuration['vcardVersion'];
        $table = $this->configuration['eventTablename'];

        $icalService = GeneralUtility::makeInstance(IcalService::class);
        $ical = $icalService->generateIcalFromRecord($recordUid, $table);

        return $ical;
    }
}
