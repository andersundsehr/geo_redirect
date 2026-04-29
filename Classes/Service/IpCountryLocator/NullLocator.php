<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Service\IpCountryLocator;

use Psr\Http\Message\ServerRequestInterface;

final class NullLocator implements IpCountryLocatorInterface
{
    public function getIpCountry(?ServerRequestInterface $request): null
    {
        return null;
    }

    public function getDebugInfo(?ServerRequestInterface $request): string
    {
        return '';
    }
}
