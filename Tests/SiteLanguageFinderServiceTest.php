<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Tests;

use AUS\GeoRedirect\Dto\BeforeSiteLanguageFinderEvent;
use AUS\GeoRedirect\Service\IpCountryLocator\IpCountryLocatorInterface;
use AUS\GeoRedirect\Service\IpCountryLocator\SucuriHeader;
use Generator;
use GuzzleHttp\Psr7\ServerRequest;
use AUS\GeoRedirect\Service\IpCountryLocator\NullLocator;
use AUS\GeoRedirect\Service\SiteLanguageFinderService;
use Psr\EventDispatcher\EventDispatcherInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

final class SiteLanguageFinderServiceTest extends TestCase
{
    /** @var int */
    public const DE = 0;

    /**  @var int */
    public const EN = 1;

    /** @var int */
    public const EN_US = 2;

    /** @var int */
    public const JA_JP = 3;

    /** @var int */
    public const EN_JP = 4;

    /** @var int */
    public const FR = 5;

    #[Test]
    #[DataProvider('findSiteDataProvider')]
    public function findSite(SiteLanguage $expected, string $httpHeader, string $ipCountryCode, Site $site, bool $ipCountryIsMoreImportantThanLanguage = false): void
    {
        $service = new SiteLanguageFinderService(new NullLocator(), new NoopEventDispatcher(), $ipCountryIsMoreImportantThanLanguage);

        $result = $service->findLanguage($httpHeader, $ipCountryCode, $site);

        self::assertSame($expected->getHreflang(), $result->getHreflang());
        self::assertSame($expected, $result);
    }

    #[Test]
    public function findByRequestUsesAcceptLanguageSiteAndIpCountryFromLocator(): void
    {
        $site = self::createSite();
        $request = (new ServerRequest('GET', '/'))
            ->withHeader('accept-language', 'en-US,en;q=0.5,ja;q=0.5')
            ->withAttribute('site', $site);
        $service = new SiteLanguageFinderService(new NullLocator(), new NoopEventDispatcher());

        $result = $service->findByRequest($request);

        self::assertEquals($site->getLanguageById(self::EN_US), $result);
    }

    public static function findSiteDataProvider(): Generator
    {
        $site = self::createSite();
        yield 'nothing given => default language' => [
            'expected' => $site->getDefaultLanguage(),
            'httpHeader' => '',
            'ipCountryCode' => '',
            'site' => $site,
        ];
        yield 'de person gets de' => [
            'expected' => $site->getLanguageById(self::DE),
            'httpHeader' => 'de-DE,de;q=0.9,en;q=0.8,und;q=0.7,en-US;q=0.6,hr;q=0.5', // chrome
            'ipCountryCode' => '',
            'site' => $site,
        ];
        yield 'de firefox person gets de' => [
            'expected' => $site->getLanguageById(self::DE),
            'httpHeader' => 'de,en-US;q=0.7,en;q=0.3', // firefox
            'ipCountryCode' => '',
            'site' => $site,
        ];
        yield 'de person without fallback in header gets de' => [
            'expected' => $site->getLanguageById(self::DE),
            'httpHeader' => 'de-DE,en;q=0.8,und;q=0.7,en-US;q=0.6,hr;q=0.5', // chrome manipulated
            'ipCountryCode' => '',
            'site' => $site,
        ];
        yield 'nl person gets en-us content !? TODO' => [
            'expected' => $site->getLanguageById(self::EN_US),
            'httpHeader' => 'nl-NL,nl;q=0.8,en-US;q=0.6,en;q=0.4',
            'ipCountryCode' => '',
            'site' => $site,
        ];

        yield 'us person in us' => [
            'expected' => $site->getLanguageById(self::EN_US),
            'httpHeader' => 'en-US,en;q=0.5',
            'ipCountryCode' => 'US',
            'site' => $site,
        ];
        yield 'us person in jp' => [
            'expected' => $site->getLanguageById(self::EN_JP),
            'httpHeader' => 'en-US,en;q=0.5',
            'ipCountryCode' => 'JP',
            'site' => $site,
        ];

        yield 'fr person gets fr' => [
            'expected' => $site->getLanguageById(self::FR),
            'httpHeader' => 'fr-FR,fr;q=0.9,en;q=0.8,und;q=0.7,en-US;q=0.6,hr;q=0.5', // chrome
            'ipCountryCode' => '',
            'site' => $site,
        ];
        yield 'fr person in jp' => [
            'expected' => $site->getLanguageById(self::FR),
            'httpHeader' => 'fr-FR,fr;q=0.9,en;q=0.8,und;q=0.7,en-US;q=0.6,hr;q=0.5',
            'ipCountryCode' => 'jp',
            'site' => $site,
        ];
        yield 'fr person in jp, ipCountryIsMoreImportantThanLanguage=true' => [
            'expected' => $site->getLanguageById(self::EN_JP),
            'httpHeader' => 'fr-FR,fr;q=0.9,en;q=0.8,und;q=0.7,en-US;q=0.6,hr;q=0.5',
            'ipCountryCode' => 'jp',
            'site' => $site,
            'ipCountryIsMoreImportantThanLanguage' => true,
        ];

        yield 'bot in jp' => [
            'expected' => $site->getLanguageById(self::JA_JP),
            'httpHeader' => '',
            'ipCountryCode' => 'jp',
            'site' => $site,
        ];
        yield 'bot in en' => [
            'expected' => $site->getDefaultLanguage(),
            'httpHeader' => '',
            'ipCountryCode' => 'en',
            'site' => $site,
        ];
        yield 'bot in de' => [
            'expected' => $site->getDefaultLanguage(),
            'httpHeader' => '',
            'ipCountryCode' => 'de',
            'site' => $site,
        ];
    }

    private static function createSite(): Site
    {
        return new Site('site', 14253, [
            'languages' => [
                [
                    'languageId' => self::DE,
                    'hreflang' => 'de',
                    'locale' => 'de_DE.UTF-8',
                ],
                [
                    'languageId' => self::EN,
                    'hreflang' => 'en',
                    'locale' => 'en_EN.UTF-8',
                ],
                [
                    'languageId' => self::FR,
                    'hreflang' => 'fr',
                    'locale' => 'fr_FR.UTF-8',
                ],
                [
                    'languageId' => self::EN_US,
                    'hreflang' => 'en-us',
                    'locale' => 'en_US.UTF-8',
                ],
                [
                    'languageId' => self::JA_JP,
                    'hreflang' => 'ja-jp',
                    'locale' => 'ja_JP.UTF-8',
                ],
                [
                    'languageId' => self::EN_JP,
                    'hreflang' => 'en-jp',
                    'locale' => 'en_US.UTF-8',
                ],
            ],
        ]);
    }
}
