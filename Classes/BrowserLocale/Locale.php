<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\BrowserLocale;

final class Locale
{
    public function __construct(
        /**
         * Full locale with language and optional country code.
         */
        public string $locale,
        /**
         * Language code of the locale.
         */
        public string $language,
        /**
         * Country code of the locale, if available.
         */
        public string $country,
        /**
         * An indicator of importance, where 1.0 is most
         * important and 0.0 is least important.
         */
        public float $weight
    ) {
    }
}
