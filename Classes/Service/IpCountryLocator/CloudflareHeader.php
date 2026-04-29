<?php

declare(strict_types=1);

namespace AUS\GeoRedirect\Service\IpCountryLocator;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequestFactory;

final class CloudflareHeader implements IpCountryLocatorInterface
{
    public function getIpCountry(?ServerRequestInterface $request): ?string
    {
        if (!$request) {
            return null;
        }

        $country = strtolower($request->getHeaderLine('CF-IPCountry'));
        if ($country === 'xx') {
            return null;
        }

        return $country ?: null;
    }

    public function getDebugInfo(?ServerRequestInterface $request): string
    {
        return '';
    }
}
