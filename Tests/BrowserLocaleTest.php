<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Tests;

use AUS\GeoRedirect\BrowserLocale\BrowserLocale;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BrowserLocaleTest extends TestCase
{
    #[Test]
    public function getLocalesReturnsLocalesSortedByWeightDescending(): void
    {
        $browserLocale = new BrowserLocale('de-DE;q=0.6, en-US;q=1.0, fr-FR;q=0.8');

        $locales = $browserLocale->getLocales();

        self::assertSame('en-US', $locales[0]->locale);
        self::assertSame('fr-FR', $locales[1]->locale);
        self::assertSame('de-DE', $locales[2]->locale);
    }

    #[Test]
    public function getLocaleReturnsMostPreferredLocale(): void
    {
        $browserLocale = new BrowserLocale('de-DE;q=0.5, en-US;q=0.9');

        $locale = $browserLocale->getLocale();

        self::assertNotNull($locale);
        self::assertSame('en-US', $locale->locale);
        self::assertSame('en', $locale->language);
        self::assertSame('US', $locale->country);
        self::assertSame(0.9, $locale->weight);
    }

    #[Test]
    #[DataProvider('localeParsingDataProvider')]
    public function parsesLocaleParts(string $header, string $expectedLocale, string $expectedLanguage, string $expectedCountry, float $expectedWeight): void
    {
        $browserLocale = new BrowserLocale($header);

        $locale = $browserLocale->getLocale();

        self::assertNotNull($locale);
        self::assertSame($expectedLocale, $locale->locale);
        self::assertSame($expectedLanguage, $locale->language);
        self::assertSame($expectedCountry, $locale->country);
        self::assertSame($expectedWeight, $locale->weight);
    }

    public static function localeParsingDataProvider(): Generator
    {
        yield 'language and country with explicit weight' => ['de-DE;q=0.7', 'de-DE', 'de', 'DE', 0.7];
        yield 'language and country without weight defaults to one' => ['en-US', 'en-US', 'en', 'US', 1.0];
        yield 'language only without country' => ['fr', 'fr', 'fr', '', 1.0];
        yield 'empty header produces empty locale' => ['', '', '', '', 1.0];
    }
}
