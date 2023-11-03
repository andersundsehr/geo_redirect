<?php

namespace AUS\GeoRedirect\Service\IpCountryLocator;

final class NullLocator implements IpCountryLocatorInterface
{
    public function getIpCountry(): ?string
    {
        return null;
    }

    public function getDebugInfo(): string
    {
        return '';
    }
}
