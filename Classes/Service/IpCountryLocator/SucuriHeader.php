<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Service\IpCountryLocator;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequestFactory;

final class SucuriHeader implements IpCountryLocatorInterface
{
    public function getIpCountry(?ServerRequestInterface $request): ?string
    {
        if (!$request) {
            return null;
        }

        return strtolower($request->getHeaderLine('X-Sucuri-Country')) ?: null;
    }

    public function getDebugInfo(?ServerRequestInterface $request): string
    {
        return '';
    }
}
