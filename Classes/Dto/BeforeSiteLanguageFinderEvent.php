<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Dto;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

final class BeforeSiteLanguageFinderEvent
{
    /**
     * if this property is set, the SiteLanguageFinderService will return this SiteLanguage
     */
    public ?SiteLanguage $siteLanguage = null;

    public function __construct(
        public readonly string $httpHeader,
        public readonly string $ipCountryCode,
        public readonly Site $site
    ) {
    }
}
