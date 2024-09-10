<?php

namespace TRAW\Vcfqr\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TRAW\Vcfqr\Service\VCardService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

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

        $fileName = 'event.ics';
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename=' . $fileName);

        print $ics;
        exit;
    }

    protected function validateUidsExist(int $addressUid, int $pageUid): bool
    {
        $cp = GeneralUtility::makeInstance(ConnectionPool::class);
        $pageResult = $cp->getConnectionForTable('pages')->select(['uid'], 'pages', ['uid' => $pageUid, 'hidden' => 0, 'deleted' => 0], [], [], 1)->fetchOne();
        $addressResult = $cp->getConnectionForTable('tt_address')->select(['uid'], 'tt_address', ['uid' => $addressUid, 'hidden' => 0, 'deleted' => 0], [], [], 1)->fetchOne();

        return $pageResult === $pageUid && $addressResult === $addressUid;
    }


    protected function fetchVcard(int $addressUid): ?array
    {
        $vcfService = GeneralUtility::makeInstance(VcardService::class);
        $vCard = $vcfService->generateVCardFromRecord($addressUid);

        return $vcard;

//        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('pages');
//        $event = $qb->select('uid', 'title', 'subtitle', 'event_datetime', 'event_archive')
//            ->from('pages')
//            ->where(
//                $qb->expr()->eq('uid', $qb->createNamedParameter($pageUid, \PDO::PARAM_INT))
//            )
//            ->execute()
//            ->fetchAssociative();
//
//
//        if ($event === false || (int)$event['event_datetime'] === 0 || (int)$event['event_archive'] === 0) {
//            return null;
//        }
//
//        $begin = new \DateTime();
//        $begin->setTimestamp($event['event_datetime']);
//
//
//        $end = new \DateTime();
//        //plus 1 day since the "DTEND" property specifies the non-inclusive end of the event.
//        $end->setTimestamp((int)$event['event_archive'] + 86400);
//
//
//        $eventBegin = $begin->format('Ymd');
//        $eventEnd = $end->format('Ymd');
//
//        $interval = $begin->diff($end);
//
//        $now = new \DateTime();
//        $currentTime = $now->format('Ymd\THis');
//        $summary = html_entity_decode($event['nav_title'] ?? $event['title'], ENT_COMPAT, 'UTF-8');
//        /** @var Uri $uri */
//
//        $eventUrl = $this->request->getUri()->withQuery('')->__toString();
//
//        $description = !empty($event['subtitle']) ? $event['subtitle'] . "\\n\\n" : '';
//
//        $description .= $eventUrl;
//        $description = html_entity_decode($description, ENT_COMPAT, 'UTF-8');
//
//        $eol = "\r\n";
//        $icalContent =
//            'BEGIN:VCALENDAR' . $eol .
//            'VERSION:2.0' . $eol .
//            'PRODID:-//Weinig Experience//experience.weinig.com//DE' . $eol .
//            'CALSCALE:GREGORIAN' . $eol .
//            'BEGIN:VEVENT' . $eol .
//            'DTSTART;VALUE=DATE:' . $eventBegin . $eol;
//
//        if ($interval->d || $interval->m || $interval->y) {
//            $icalContent .= 'DTEND;VALUE=DATE:' . $eventEnd . $eol;
//        }
//
//        $icalContent .=
//            'DTSTAMP:' . $currentTime . $eol .
//            'SUMMARY:' . $summary . $eol .
//            'URL;VALUE=URI:' . $eventUrl . $eol .
//            'DESCRIPTION:' . $description . $eol .
//            'UID:' . $currentTime . '-' . $eventBegin . '-' . $eventEnd . $eol .
//            'END:VEVENT' . $eol .
//            'END:VCALENDAR';
//
//        return $icalContent;
    }
}