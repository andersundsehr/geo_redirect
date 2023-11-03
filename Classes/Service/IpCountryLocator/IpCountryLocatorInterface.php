<?php

namespace AUS\GeoRedirect\Service\IpCountryLocator;

interface IpCountryLocatorInterface
{
    /**
     * Get "ISO 3166 ALPHA-2" code
     *
     * @see https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
     */
    public function getIpCountry(): ?string;

    public function getDebugInfo(): string;
}
