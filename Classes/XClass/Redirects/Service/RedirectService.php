<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\XClass\Redirects\Service;

use AUS\GeoRedirect\Service\SiteLanguageFinderService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class RedirectService extends \TYPO3\CMS\Redirects\Service\RedirectService
{
    private SiteLanguage $siteLanguage;

    /**
     * @param array<mixed> $matchedRedirect
     */
    public function getTargetUrl(array $matchedRedirect, ServerRequestInterface $request): ?UriInterface
    {
        $siteLanguageFinderService = GeneralUtility::makeInstance(SiteLanguageFinderService::class);
        assert($siteLanguageFinderService instanceof SiteLanguageFinderService);
        $this->siteLanguage = $siteLanguageFinderService->findByRequest($request);
        return parent::getTargetUrl($matchedRedirect, $request);
    }

    /**
     * @inheritdoc
     * @return array<string, mixed>
     */
    protected function resolveLinkDetailsFromLinkTarget(string $redirectTarget): array
    {
        $linkDetails = parent::resolveLinkDetailsFromLinkTarget($redirectTarget);
        if ($linkDetails['type'] === 'page') {
            parse_str($linkDetails['parameters'] ?? '', $query);
            $query['L'] = $this->siteLanguage->getLanguageId();
            $linkDetails['parameters'] = http_build_query($query);
        }

        return $linkDetails;
    }
}
