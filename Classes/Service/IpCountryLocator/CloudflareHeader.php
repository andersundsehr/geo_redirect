<?php

namespace AUS\GeoRedirect\Service\IpCountryLocator;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequestFactory;

final class CloudflareHeader implements IpCountryLocatorInterface
{
    public function getIpCountry(): ?string
    {
        if (Environment::isCli()) {
            return null;
        }

        $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
        assert($request instanceof ServerRequestInterface);
        $country = strtolower($request->getHeaderLine('CF-IPCountry'));
        if ($country === 'xx') {
            return null;
        }

        return $country ?: null;
    }

    public function getDebugInfo(): string
    {
        return '';
    }
}
