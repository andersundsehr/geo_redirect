<?php

namespace AUS\GeoRedirect\BrowserLocale;

use TYPO3\CMS\Core\Utility\GeneralUtility;

final class BrowserLocale
{
    /** @var list<Locale> */
    private array $locales;

    public function __construct(string $httpAcceptLanguages)
    {
        $this->locales = $this->parseHttpAcceptLanguages($httpAcceptLanguages);
    }

    /**
     * Get the most preferred locale.
     */
    public function getLocale(): ?Locale
    {
        return $this->locales[0] ?? null;
    }

    /**
     * Get an array of Locale objects in descending order of preference.
     *
     * @return array<Locale>
     */
    public function getLocales(): array
    {
        return $this->locales;
    }

    /**
     * Parse all HTTP Accept Languages.
     *
     * @return list<Locale>
     */
    private function parseHttpAcceptLanguages(string $httpAcceptLanguages): array
    {
        $acceptLanguages = GeneralUtility::trimExplode(',', $httpAcceptLanguages);
        $locales = [];

        foreach ($acceptLanguages as $httpAcceptLanguage) {
            $locales[] = $this->makeLocale($httpAcceptLanguage);
        }

        return $this->sortLocales($locales);
    }

    /**
     * Convert the given HTTP Accept Language to a Locale object.
     */
    private function makeLocale(string $httpAcceptLanguage): Locale
    {
        $parts = GeneralUtility::trimExplode(';', $httpAcceptLanguage);

        $locale = $parts[0];
        $weight = $parts[1] ?? '';

        return new Locale(
            $locale,
            $this->getLanguage($locale),
            $this->getCountry($locale),
            $this->getWeight($weight)
        );
    }

    /**
     * Get the 2-letter language code from the locale.
     */
    private function getLanguage(string $locale): string
    {
        return substr($locale, 0, 2) ?: '';
    }

    /**
     * Get the 2-letter country code from the locale.
     */
    private function getCountry(string $locale): string
    {
        return substr($locale, 3, 2) ?: '';
    }

    /**
     * Parse the relative quality factor and return its value.
     */
    private function getWeight(string $qualityFactor): float
    {
        $parts = GeneralUtility::trimExplode('=', $qualityFactor);

        $weight = $parts[1] ?? 1.0;

        return (float)$weight;
    }

    /**
     * Sort the array of locales in descending order of preference.
     *
     * @param list<Locale> $locales
     * @return list<Locale>
     */
    private function sortLocales(array $locales): array
    {
        usort($locales, fn($localeA, $localeB): int => $localeB->weight <=> $localeA->weight);

        return $locales;
    }
}
