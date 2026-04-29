<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\XClass\Redirects\Service;

use AUS\GeoRedirect\Service\SiteLanguageFinderService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function http_build_query;
use function parse_str;

/**
 * Overrides TYPO3's redirect service to generate page redirect targets for the
 * site language selected by this extension's language finder.
 *
 * If the translated target cannot be resolved, the service retries without
 * forcing the resolved language so the core fallback URL can still be used.
 */
trait RedirectServiceTrait
{
    /**
     * @param array{target: string} $matchedRedirect
     */
    public function getTargetUrl(array $matchedRedirect, ServerRequestInterface $request): ?UriInterface
    {
        $originalTarget = $matchedRedirect['target'];
        $linkDetails = parent::resolveLinkDetailsFromLinkTarget($matchedRedirect['target']);
        if ($linkDetails['type'] === 'page') {
            parse_str($linkDetails['parameters'] ?? '', $query);
            // set the found langaugeId:
            $query['_language'] = GeneralUtility::makeInstance(SiteLanguageFinderService::class)->findByRequest($request)->getLanguageId();
            unset($query['L']);
            $linkDetails['parameters'] = http_build_query($query);

            $matchedRedirect['target'] = GeneralUtility::makeInstance(LinkService::class)->asString($linkDetails);
        }

        $url = parent::getTargetUrl($matchedRedirect, $request);
        if (null !== $url) {
            return $url;
        }

        // not found in targeted langauge, so we use the original Target as fallback:
        $matchedRedirect['target'] = $originalTarget;

        return parent::getTargetUrl($matchedRedirect, $request);
    }
}
