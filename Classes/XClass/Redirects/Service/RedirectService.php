<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\XClass\Redirects\Service;

use AUS\GeoRedirect\Dto\OverwriteSiteLanguage;
use AUS\GeoRedirect\Service\SiteLanguageFinderService;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

final class RedirectService extends \TYPO3\CMS\Redirects\Service\RedirectService
{
    /**
     * @param array<string, mixed> $queryParams
     */
    protected function bootFrontendController(SiteInterface $site, array $queryParams, ServerRequestInterface $originalRequest): TypoScriptFrontendController
    {
        $siteLanguageFinderService = GeneralUtility::makeInstance(SiteLanguageFinderService::class);
        assert($siteLanguageFinderService instanceof SiteLanguageFinderService);
        $siteLanguage = $siteLanguageFinderService->findByRequest($originalRequest);
        $manipulatedSite = new OverwriteSiteLanguage($site, $siteLanguage);

        try {
            return parent::bootFrontendController($manipulatedSite, $queryParams, $originalRequest);
        } catch (Throwable $throwable) {
            // fallback if page is not in the correct language:
            if ($throwable->getCode() === 1533931402) {
                return parent::bootFrontendController($site, $queryParams, $originalRequest);
            }

            throw $throwable;
        }
    }
}
