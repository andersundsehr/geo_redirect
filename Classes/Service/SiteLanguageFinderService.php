<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Service;

use AUS\GeoRedirect\Dto\BeforeSiteLanguageFinderEvent;
use AUS\GeoRedirect\Service\IpCountryLocator\IpCountryLocatorInterface;
use CodeZero\BrowserLocale\BrowserLocale;
use CodeZero\BrowserLocale\Locale;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

use function assert;

#[Autoconfigure(public: true)]
final readonly class SiteLanguageFinderService
{
    public function __construct(
        private IpCountryLocatorInterface $ipCountryLocator,
        private EventDispatcherInterface $eventDispatcher,
        private string|bool $ipCountryIsMoreImportantThanLanguage = false,
    ) {
    }

    public function findByRequest(?ServerRequestInterface $request = null): SiteLanguage
    {
        $request ??= $GLOBALS['TYPO3_REQUEST'] ?? throw new RuntimeException('No request found This api is not working in cli mode', 9665405638);
        assert($request instanceof ServerRequestInterface);

        $site = $request->getAttribute('site');
        assert($site instanceof Site);
        return $this->findLanguage(
            $request->getHeaderLine('accept-language'),
            $this->ipCountryLocator->getIpCountry() ?? '',
            $site
        );
    }

    public function findLanguage(string $httpHeader, string $ipCountryCode, Site $site): SiteLanguage
    {
        $httpHeader = strtolower($httpHeader);
        $ipCountryCode = strtolower($ipCountryCode);
        $event = $this->eventDispatcher->dispatch(new BeforeSiteLanguageFinderEvent($httpHeader, $ipCountryCode, $site));
        assert($event instanceof BeforeSiteLanguageFinderEvent);
        if ($event->siteLanguage) {
            return $event->siteLanguage;
        }

        $siteLanguages = [];
        foreach ($site->getLanguages() as $language) {
            $hreflang = strtolower($language->getHreflang());
            $siteLanguages[$hreflang] = $language;

            $explode = explode('-', $hreflang);
            $siteLanguages[$explode[0] . '-'] ??= $language;
            if ($explode[1] ?? null) {
                $siteLanguages['-' . $explode[1]] ??= $language;
            }
        }

        $browserLocale = new BrowserLocale($httpHeader);

        $ipCountryCode = $ipCountryCode ?: 'xx';

        // if ipCountry is more important than language, then this should be active
        if ($this->ipCountryIsMoreImportantThanLanguage) {
            // foreach language test if that language  + ipCountry is found
            foreach ($browserLocale->getLocales() as $locale) {
                assert($locale instanceof Locale);

                $siteLanguage = $siteLanguages[$locale->language . '-' . $ipCountryCode] ?? null;
                if ($siteLanguage) {
                    return $siteLanguage;
                }
            }
        }

        // foreach language test:
        foreach ($browserLocale->getLocales() as $locale) {
            assert($locale instanceof Locale);

            $siteLanguage =
                // test if language is found with country from ip
                $siteLanguages[$locale->language . '-' . $ipCountryCode]
                // test if language is found with country from header
                ?? $siteLanguages[$locale->language . '-' . $locale->country]
                // test if language is found with any country in site
                ?? $siteLanguages[$locale->language . '-']
                ?? null;
            if ($siteLanguage) {
                return $siteLanguage;
            }

            // test next language of header
        }

            // test if ipCountry is found in site
        return $siteLanguages['-' . $ipCountryCode] ?? $site->getDefaultLanguage();
    }
}
